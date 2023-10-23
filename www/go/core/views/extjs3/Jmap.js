go.Jmap = {

	requests: [],

	requestOptions: {},

	callId: 0,

	timeout: null,

	paused: 0,

	requestTimeout: 180000,

	/**
	 * Enable for XDEBUG profiling
	 */
	profile: false,

	/**
	 * Server capabilities. It's set when auth request completes in authentication manager
	 */
	capabilities : {
		maxSizeUpload: 100*1000*1024,

		maxConcurrentUpload: 4,

		maxSizeRequest:  100*1000*1024,

		maxConcurrentRequests: 4,

		maxCallInRequest: 10,

		maxObjectsInGet: 100,

		maxObjectsInSet: 1000
	},

	nextCallId: function () {
		this.callId++;

		return this.callId;
	},

	debug: function () {
		this.scheduleRequest({
			method: 'community/dev/Debugger/get',
			params: {},
			callback: function(options, success, response, clientCallId) {
				this.processDebugResponse(response, clientCallId);
			},
			scope: this
		}).catch(function() {
			//ignore error
		});
	},

	processDebugResponse : function(response, clientCallId) {
		var r;
		while(r = response.shift()) {
			var method = r.shift();
			r.push(clientCallId);
			//escape % for console.log
			r = r.map(function(i) {
				if(Ext.isString(i)) {
					i = i.replace(/%/g, "%%");
				}
				return i;
			});
			console[method].apply(null, r);
		}
	},

	abort: function (clientCallId) {
		console.warn("Abort request " + clientCallId);

		for (var i = 0, l = this.requests.length; i < l; i++) {
			if (this.requests[i][2] == clientCallId) {
				this.requests.splice(i, 1);
				break;
			}
		}

		delete this.requestOptions[clientCallId];
	},
	
	/**
	 * Find 
	 * @param {string} method
	 * @returns {Array|Boolean}
	 */
	findRequestByMethod: function(method) {
		for(var i = 0, l = this.requests.length; i < l; i++) {
			if(this.requests[i][0] == method) {
				return this.requests[i];
			}
		}
		
		return false;
	},
	
	getApiUrl : function() {
		var url = BaseHref + 'api/jmap.php';

		if(this.profile) {
			url += '?XDEBUG_PROFILE=1';
		}
		return url;
	},

	/**
	 * Returns session object
	 *
	 * @returns {Promise<unknown>}
	 */
	get: function() {

		return new Promise((resolve, reject) => {
			Ext.Ajax.request({
				url: this.getApiUrl(),
				method: 'GET',
				callback: function (opts, success, response) {

					if(success) {
						var data = Ext.decode(response.responseText);

						resolve(data);
					} else
					{
						reject(response);
					}
				}
			});
		})
	},
	
	downloadUrl: function(blobId, inline) {
		if (!blobId) {
			return '';
		}
		var url = go.User.downloadUrl.replace('{blobId}', blobId);
		if(inline) {
			url += '&inline=1';
		}

		return url;
	},

	pageUrl: function(route) {
		if(route.substr(0,1) != '/') {
			route = "/" + route;
		}
		var url = go.User.pageUrl + route
		return url;
	},

	thumbUrl: function(blobId, params) {
		if (!blobId) {
			return '';
		}
		var url = BaseHref + 'api/thumb.php?blob=' + blobId;

		for(var name in params) {
			url += '&' + name + '=' + encodeURIComponent(params[name]);
		}

		return url;

	},




	upload : function(file, cfg) {

		go.Uploader.addFile(file, cfg);
	},


	/**
	 * When SSE is disabled we'll poll the server for changes every 2 minutes.
	 * This also keeps the token alive. Which expires in 30M.
	 */
	poll : function() {

		if(this.pollInterval) {
			console.log("Poll already running");
		}
		console.log("Start check for updates every 60s.");
		const checkFn = function() {
			go.Db.stores().forEach(function(store) {
				store.getState().then(function(state) {
					if (state)
						store.getUpdates();
				});
			})

		};
		checkFn();
		this.pollInterval = setInterval(checkFn, 60000);
	},

	pollInterval: undefined,
	eventSource: undefined,

	stopSse() {
		console.log("Closing SSE");
		if(this.pollInterval) {
			clearInterval(this.pollInterval);
			this.pollInterval = undefined
		}

		if(this.eventSource) {
			this.eventSource.close();
			this.eventSource = undefined;
		}
	},
	
	/**
	 * Initializes Server Sent Events via EventSource. This function is called in MainLayout.onAuthenticated()
	 * 
	 * Note: disable this if you want to use xdebug because it will crash if you use SSE.
	 * 
	 * @returns {Boolean}
	 */
	sse : function() {
		try {

			if(!this.SSEEventsRegistered) {
				this.registerSSEEvents();
			}

			if (!window.navigator.onLine){
				console.log("SSE not stated because we're offline");
				return false;
			}

			// if (document.visibilityState !== "visible") {
			// 	console.log("SSE not stated because we're not visible");
			// 	return false;
			// }

			if (!window.EventSource) {
				console.debug("Browser doesn't support EventSource");
				this.poll();
				return false;
			}
			
			if(!go.User.eventSourceUrl) {
				console.debug("Server Sent Events (EventSource) is disabled on the server.");
				this.poll();
				return false;
			}

			if(this.eventSource) {
				console.log("SSE already running");
				return;
			}

			console.log("Starting SSE");
			
			//filter out legacy modules
			var entities = go.Entities.getAll().filter(function(e) {
				return e.package != "legacy" && e.name != "Search";
			});
			
			var url = go.User.eventSourceUrl + '?types=' + 
							entities.column("name").join(',');
			
			this.eventSource = new EventSource(url), me = this;

			this.eventSource.addEventListener('msg', function(e) {
				go.Notifier.flyout({title:"New message",description: event.data, time: 5000});
			});

			this.eventSource.addEventListener('exception', function(e) {
				console.error(e);
			});

			this.eventSource.addEventListener('state', function(e) {

				var data = JSON.parse(e.data);

				for(var entity in data) {
					var store = go.Db.store(entity);
					if(store) {
						(function(store) {
							store.getState().then(function(state) {
								// console.warn(store.entity.name, state);
								if(!state || state == data[store.entity.name]) {
									//don't fetch updates if there's no state yet because it never was used in that case.
									return;
								}
								
								store.getUpdates().catch((e) => {
									console.warn(e);
									//ignore changes error, sync will reset on error
								});
							});
						})(store);
					}
				}
			}, false);



		}
		catch(e) {
			console.error("Failed to start Server Sent Events. Perhaps the API URL in the system settings is invalid?", e);
		}
	},


	registerSSEEvents: function() {

		this.SSEEventsRegistered = true;

		// window.addEventListener('beforeunload', () => {
		// 	console.log("Closing SSE")
		// 	go.Jmap.stopSse();
		// });

		window.addEventListener('offline', () => {
			console.log("Closing SSE because we're offline")
			go.Jmap.stopSse();
		});

		window.addEventListener('online', () => {
			console.log("Starting SSE because we're online")
			go.Jmap.sse();
		});

		// document.addEventListener("visibilitychange", (e) => {
		// 	console.log("visibilitychange", document.visibilityState);
		// 	if (document.visibilityState === "visible") {
		// 	} else {
		// 		go.Jmap.stopSse();
		// 	}
		// });
	},

	/**
	 * 
	 * 
	 * @param {type} options
	 * 
	 * An object containing:
	 * 
	 * method: jmap method
	 * params: jmap method parameters
	 * callback: Deprecated! Use the promise functionality. If you pass a callback you can't use the promise. Function to call after request. Arghuments are options, success, response.
	 * scope: callback function scope
	 * dispatchAfterCallback: dispatch the response after the callback. Defaults to false.
	 * 
	 * @returns {Promise} resolved with response
	 */
	request: function (options) {
		if(!options.method) {
			throw "method is required";
		}

		if (this.timeout) {
			clearTimeout(this.timeout);
			this.timeout = null;
		}
		
		var promise = this.scheduleRequest(options);

		if(!this.paused) {
			this.delayedProcessQueue();
		}
		if(!options.callback) {
			return promise;
		}
	},

	scheduleRequest: function(options) {
		var clientCallId = "clientCallId-" + this.nextCallId(), promise = new Promise(function(resolve, reject){
			options.resolve = resolve;
			options.reject = reject;
		});
		promise.callId = clientCallId;

		this.requests.push([options.method, options.params || {}, clientCallId]);
		this.requestOptions[clientCallId] = options;

		return promise;
	},

	/**
	 * Pause request execution
	 */
	pause : function() {

		// if(!GO.pauseCalls) {
		// 	GO.pauseCalls = 1;
		// } else {
		// 	GO.pauseCalls++
		// }
		// console.trace("pause", GO.pauseCalls);

		this.paused++;
		if (this.timeout) {
			clearTimeout(this.timeout);
			this.timeout = null;
		}
	},

	/**
	 * Continue request event execution as the next macro task.
	 */
	continue: function() {

		// if(!GO.continueCalls) {
		// 	GO.continueCalls = 1;
		// } else {
		// 	GO.continueCalls++
		// }
		// console.trace("continue", GO.continueCalls);

		if(this.paused > 0) {
			this.paused--;
		}

		if(this.paused > 0)
		{
			return;
		}

		this.delayedProcessQueue();
	},

	delayedProcessQueue : function() {
		if (this.timeout) {
			clearTimeout(this.timeout);
		}
		var me = this;
		this.timeout = setTimeout(function() {
			me.processQueue();
		}, 0);
	},

	processQueue: function () {

		this.timeout = null;

		if (!this.requests.length) {
			//All requests aborted
			return;
		}

		if(GO.debug || GO.settings.config.debug) {
			this.debug();
		}

		Ext.Ajax.request({
			timeout: this.requestTimeout,
			url: this.getApiUrl(),
			method: 'POST',
			jsonData: this.requests,
			scope: this,
			success: function (response, opts) {
				// try {
					var responses = JSON.parse(response.responseText);

					responses.forEach(function (response) {

						var clientCallId = response[2];

						//lookup request options by client ID
						var o = this.requestOptions[clientCallId], me = this;
						if (!o) {
							//aborted
							console.debug("Aborted");
							return true;
						}
						if (response[0] == "error") {
							console.error('server-side JMAP failure', response);							
						}

							var success = response[0] !== "error";
							if (o.callback) {
								if (!o.scope) {
									o.scope = this;
								}
								o.callback.call(o.scope, o, success, response[1], clientCallId);
							} else {

								//response[1].options = o;

								if (success) {
									o.resolve(response[1]);
								} else {
									o.reject(response[1]);
								}
							}

							delete me.requestOptions[clientCallId];

					}, this);

				// } catch(e) {					
				// 	console.error(e,"server reponse:", response.responseText);

				// 	Ext.MessageBox.alert(t("Error"), t("An error occured on the server. The console shows details."))
				// }
			},
			failure: function (response, opts) {

				for(var i = 0, l = opts.jsonData.length; i < l; i++) {
					var clientCallId = opts.jsonData[i][2];
					this.requestOptions[clientCallId].reject({message: response.responseText});
					delete this.requestOptions[clientCallId];
				}

				if(response.isAbort) {
					console.warn('Connection aborted', response);
					return;
				}

				if(response.isTimeout || response.status == 0) {
					console.warn((new Date()).format("Y-m-d G:i:s")+ ": Connection timeout", response, opts);
					if(document.visibilityState === "visible") {
						GO.errorDialog.show(t("The request timed out. The server took too long to respond. Please try again."));
					}
					return;
				}
				console.error('server-side failure with status code ' + response.status);
				console.error(response);


				if(response.status !== 504) {// gateway timeout

					let msg = t("Sorry, an error occurred");

					if(response.responseText) {
						msg += ": " + response.responseText;
					}

					GO.errorDialog.show(msg);
				}
				
			}
		});

		this.requests = [];

	}
};
