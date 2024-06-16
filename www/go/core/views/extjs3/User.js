go.User = new (Ext.extend(Ext.util.Observable, {
	loaded : false,
	authenticate: function(cb, scope) {
		return this.load();
	},

	load : function() {
		return go.Jmap.get().then((data) => {
			return this.onLoad(data);
		});
	},

	onLoad : function(session) {

		// we only want this in a httpOnly cookie for security
		delete session.accessToken;

		// Needed for every non-GET request when using the access token as cookie.
		Ext.Ajax.defaultHeaders['X-CSRF-Token'] = session.CSRFToken;

		this.capabilities = go.Jmap.capabilities = session.capabilities;
		this.session = session;

		this.apiUrl = session.apiUrl;
		this.downloadUrl = session.downloadUrl;
		this.uploadUrl = session.uploadUrl;
		this.pageUrl = session.pageUrl;
		this.eventSourceUrl = session.eventSourceUrl;		
		this.loaded = true;
		this.apiVersion = session.version + "-" + session.cacheClearedAt;

		GO.settings.state = session.state;

		const prefersColorQuery = window.matchMedia('(prefers-color-scheme: dark)'),
			changeTheme = e => {
				if(document.body.classList.contains('system'))
					document.body.classList[e.matches ? 'add':'remove']('dark')
			}
		prefersColorQuery.addEventListener('change', changeTheme);

		// Ext.apply(this, session.user);
		return go.Db.store("User").single(session.userId).then((user) => {
			Ext.apply(this, user);
			// me.firstWeekDay = parseInt(user.firstWeekday);
			this.legacySettings(user);

			this.checkForNewDevices(user);
			document.body.classList.add(user.themeColorScheme);
			changeTheme(prefersColorQuery);
			if(document.body.classList.contains("dark")) {
				document.getElementsByTagName("meta")["theme-color"].content = "#202020";
			}

			go.ActivityWatcher.activity();
			go.ActivityWatcher.init(GO.settings.config.logoutWhenInactive);

			this.fireEvent("load", this);

			return this;
		});
		
	},

	checkForNewDevices(user) {
		for(const id in user.clients) {
			const client = user.clients[id];
			if(client.status === 'new') {
				Ext.Msg.show({
					title:'New ActiveSync device',
					msg: 'A new account was setup on your mobile device. Please confirm that it was you to enable this device'+
						': <br>'+client.version,
					buttons: Ext.Msg.YESNO,
					fn: (me,s) => {
						client.status = me == 'yes' ? 'allowed' : 'denied';
						go.Db.store("User").set({
							update: {[user.id]: {
								clients: user.clients
							}}
						})
					},
					icon: Ext.MessageBox.QUESTION
				});

			}
		}
	},
	
	legacySettings : function (user) {

		Ext.apply(GO.settings, {
			'user_id' : user.id
			,'avatarId' : user.avatarId
			,'has_admin_permission' : user.isAdmin
			,'username' : user.username
			,'displayName' : user.displayName
			,'email' : user.email
			,'thousands_separator' : user.thousandsSeparator
			,'decimal_separator' : user.decimalSeparator
			,'date_format' : user.dateFormat
			,'time_format' : user.timeFormat
			,'currency' : user.currency
			,'lastlogin' : user.lastLogin
			,'max_rows_list' : user.max_rows_list
			,'timezone' : user.timezone
			,'start_module' : user.start_module
			,'theme' : user.theme
			,'mute_sound' : user.mute_sound
			,'mute_reminder_sound' : user.mute_reminder_sound
			,'mute_new_mail_sound' : user.mute_new_mail_sound
			,'popup_reminders' : user.popup_reminders
			,'popup_emails' : user.popup_emails
			,'show_smilies' : user.show_smilies
			,'auto_punctuation' : user.auto_punctuation
			,'first_weekday' : user.firstWeekday
			,'sort_name' : user.sort_name
			,'list_separator' : user.listSeparator
			,'text_separatoe.r' : user.textSeparator
			,'modules' : []
		});
	},

	loadLegacyModules : function() {
			GO.settings.modules = {};
			var modules = go.Modules.getAvailable();
			for(var id in modules) {
				var m = modules[id];

				if(!m.enabled) {
					continue;
				}
				
				GO.settings.modules[m.name] = m;
				// m.url = 
				GO.settings.modules[m.name].permission_level = m.permissionLevel;
				GO.settings.modules[m.name].read_permission = !!m.permissionLevel;
				GO.settings.modules[m.name].write_permission = m.permissionLevel >= go.permissionLevels.write;
			}

	},
  
	isLoggedIn: function() {
		return !Ext.isEmpty(this.username);
	}
}));

// Update go.User when it's edited
Ext.onReady(function(){
	go.Db.store("User").on("changes", function(store, added, changed, deleted){
		if(changed.indexOf(go.User.id) > -1) {
			store.single(go.User.id).then((user) => {
				Ext.apply(go.User, user);
			});
		}
	});
})
