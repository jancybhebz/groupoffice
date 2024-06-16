go.modules.SystemSettingsModuleGrid = Ext.extend(go.systemsettings.Panel, {
	hasPermission: function() {
		return go.User.isAdmin;
	},
	iconCls: 'ic-extension',
	autoExpandColumn: 'name',
	//layout: 'fit',
	paging: false,
	clicksToEdit: 1,
	loadMask: true,
	itemId: "modules", //makes it routable
	cls: 'go-modules',
	// bodyCssClass: 'x-view-tiles go-module-tile',
	title: t('Modules'),

	autoScroll:true,

	emptyText: '<div class="empty-state"><i class="icon">cloud_upload</i><br>'+
		'<h3>'+t('No modules available')+'</h3>'+
		'<small>'+t('Check your installation folder and try running a database check')+'</small></div>',

	initComponent: function() {


		this.store = new GO.data.JsonStore({
			url: GO.url("modules/module/store"),
			fields:['name', 'package', 'status', 'localizedPackage', 'localizedName',  'description', 'id', 'sort_order', 'admin_menu', 'rights', 'icon', 'enabled', 'warning','author', 'buyEnabled','not_installable', 'isRefactored','installed'],
			remoteSort: false,
			idProperty: 'name'

		});

		this.store.on('beforeload', () => {
			this.getEl().mask(t("Loading..."));
		});

		this.store.on('load', () => {

			this.store.multiSort([
				{field: 'localizedPackage', direction: 'ASC'},
				{field: 'name', direction: 'ASC'}
			]);

			this.getEl().unmask();
		});
		this.store.on('exception', () => {
			this.getEl().unmask();
		});

		this.tbar = [{
			iconCls: 'ic-refresh',
			tooltip: t("Refresh"),
			handler: function() {
				this.store.load();
			},
			scope: this
		},{
			text:t('System') + ' ' +t('Permissions'),
			iconCls: 'ic-group',
			handler: function() {
				const corePermissionDlg = new go.modules.GroupRights();
				const coreModule = go.Db.store('Module').findBy(function(rec) {
					return (rec.name === 'core' && rec.package === 'core');
				},this)
				go.Db.store('Module').single(coreModule.id).then((core) =>
					corePermissionDlg.show(core, ['mayChangeUsers', 'mayChangeGroups', 'mayChangeCustomFields'])
				)
			}
		},this.trialButton = new Ext.Button({
			iconCls: 'ic-star',
			cls: "accent",
			text: t("Trial license", "modules"),
			hidden:true,
			handler: function() {
				const licenseDialog = new go.license.LicenseDialog();
				licenseDialog.show();
			},
			scope: this
		}),' ',{
			xtype:'buttongroup',
			items: [{
				xtype:'button',
				text:t('All'),
				pressed:true,
				toggleGroup:'installedFilter',
				handler: function() {
					delete this.store.baseParams.isInstalled;
					this.store.load();
				},scope:this
			},{
				xtype:'button',
				text:t('Installed'),
				toggleGroup:'installedFilter',
				handler: function() {
					this.store.baseParams.isInstalled = true;
					this.store.load();
				},scope:this
			},{
				xtype:'button',
				text:t('Available'),
				toggleGroup:'installedFilter',
				handler: function() {
					this.store.baseParams.isInstalled = false;
					this.store.load();
				},scope:this
			}]
		},'->',{
			xtype:'tbsearch',
			store: this.store,
			onSearch: function(v) {
				this.store.baseParams['query'] = v;
				this.store.load();
			}
		}, {
			xtype: "button",
			iconCls: "ic-sort",
			handler: () => {
				const win = new go.modules.ModuleSortWindow();
				win.show();
			}
		}];

		this.store.on('update', this.draw,this);
		this.store.on('load', this.draw,this);

		this.on('afterrender', function() {

			this.tip = new Ext.ToolTip({
				renderTo: Ext.getBody(),
				target: this.getEl(),
				delegate: ".tile",
				listeners : {
					beforeshow: function updateTipBody(tip) {
						tip.body.dom.innerHTML = tip.triggerElement.dataset.tooltip;
					}
				}
			});


			this.store.load();
		},this);

		go.modules.SystemSettingsModuleGrid.superclass.initComponent.call(this);

		const coreMod = go.Modules.get("core", "core");

		if(!coreMod.settings.license) {
			this.trialButton.show();
		}else {
			this.trialButton.hide();
		}
	},

	draw: function() {

		const store = this.store;

		this.trialButton.setVisible(!store.reader.jsonData.has_license);

		const scrollTop = this.body.dom.scrollTop;

		this.removeAll();

		let lastPackage = null;

		store.each(function(r){
			var hasEditPermission = r.get("permissionLevel") < go.permissionLevels.manage;
			var isInstalled = r.data.id != null;

			if(r.data.localizedPackage != lastPackage) {
				this.add({
					xtype: "box",
					autoEl: "h3",
					html: r.data.localizedPackage
				});
				lastPackage = r.data.localizedPackage;

				this.packageContainer = this.add({
					xtype: 'container',
					cls: 'x-view-tiles go-module-tile'
				})
			}

			this.packageContainer.add({
				xtype:'container',
				cls: 'tile',
				listeners: {
					render: function(c) {
						var author = r.data.author ? '<br><br>'+t('Author')+': '+r.data.author : '';
						c.getEl().dom.dataset.tooltip = r.data.description + author;
					}
				},
				items:[
				// 	{
				// 	xtype:'box',
				// 	cls: 'corner',
				// 	autoEl:{tag: 'span'},
				// 	html: r.data.localizedPackage
				// },{
				// 	xtype:'numberfield',
				// 	allowBlank: false,
				// 	disabled: !isInstalled,
				// 	decimals:0,
				// 	fieldLabel: t('Sort order'),
				// 	value: r.data.sort_order,
				// 	record: r,
				// 	listeners:{
				// 		change: function(btn, newValue) {
				// 			btn.record.set("sort_order", newValue);
				// 			this.submitRecord(btn.record);
				// 		},
				// 		scope:this
				// 	}
				// },
					{
						xtype:'box',
						cls: 'status ' + r.data.status,
						html: t(r.data.status)
					},
					{
						xtype:'box',
						cls: 'thumb',
						style: 'background-image:url(' + go.Jmap.downloadUrl('core/moduleIcon/'+(r.data.package || "legacy")+'/'+r.data.name) + '&mtime='+go.User.session.cacheClearedAt+')',
					}, {
						xtype: 'box',
						cls: 'text',
						style:'font-weight:bold;',
						html: r.data.localizedName
					}, {
						xtype: 'box',
						cls: 'text',
						style: 'font-size:12px;',
						html: r.data.description,
					},{
						xtype:'toolbar',
						items:[{
							xtype:'button',
							cls: isInstalled ? 'primary' : '',
							isInstalled : isInstalled,
							enableToggle: true,
							pressed: r.data.enabled,
							text: r.data.enabled ? t('Disable') : (isInstalled ? t('Enable') : t('Install')),
							disabled: r.data.not_installable,
							record: r,
							handler: function(btn) {
								btn.record.set("enabled", !btn.record.data.enabled);
								this.enableModule(btn.record);
							},
							scope:this
						}, '->', {
							xtype:'button',
							iconCls: 'ic-share',
							disabled: !isInstalled,
							record: r,
							handler: function(btn) {
								var record = btn.record;

								this.showRights(record.data.id, record.data.rights);
							},
							scope: this
						}, {
							xtype:'button',
							iconCls: 'ic-delete',
							disabled: !isInstalled,
							record: r,
							handler: function(btn) {this.deleteModule(btn.record);},
							scope:this
						}]
					}
				]
			});
		},this);

		if(store.getTotalCount() < 1) {
			this.add({
				xtype: 'box', html: '<div class="empty-state"><i class="icon">cloud_upload</i><br>' +
					'<h3>' + t('No modules available') + '</h3>' +
					'<small>' + t('Check your installation folder and try running a database check') + '</small></div>'
			});
		}
		this.doLayout();

		this.body.dom.scrollTop = scrollTop;

	},

	showRights: function( id, rights) {
		const dlg = new go.modules.GroupRights();
		go.Db.store('Module').single(id).then((module) =>
			dlg.show(module, rights)
		);
	},

	warningRenderer: function(name, cell, record) {
		return record.data.warning != '' ?
			'<div class="go-icon go-warning-msg" ext:qtip="' + Ext.util.Format.htmlEncode(record.data.warning) + '"></div>' : '';
	},

	enableModule: function(record) {

		if(record.data.isRefactored) {
			return this.submitJmap(record);
		}

		this.getEl().mask(t("Saving..."));

		GO.request({
			maskEl:this.getEl(),
			url: 'modules/module/update',
			params: {
				id: record.data.name,
				enabled: record.data.enabled
			},
			scope: this,
			success: function(response, options, result) {

				if (result.id) {
					record.set('aclId', result.aclId);
					record.set('id', result.id);
					record.set("enabled", record.data.enabled);
					if (record.data.enabled) {
						this.showRights(result.id, record.data.rights);
						//this.store.load();
					}
				}
				record.commit();

				this.draw();
				this.getEl().unmask();


			}
		});
	},

	submitRecord : function(record){

		if(record.data.isRefactored) {
			return this.submitJmap(record);
		}

		var url = GO.url('modules/module/updateModuleModel');
		this.getEl().mask(t("Loading..."));
		Ext.Ajax.request({
			method:'POST',
			url: url,
			params : {
				id:record.data.name
			},
			jsonData: {module:this.createJSON(record.data)},
			scope : this,
			callback : function (options, success,response) {

				this.getEl().unmask();
				var responseParams = Ext.decode(response.responseText);

				if (!responseParams.success) {
					GO.errorDialog.show(responseParams.feedback);
					this.store.load();
				}else{
					if(responseParams.id){
						record.set('id', responseParams.id);
					}
					record.commit();
				}

				this.draw();
				this.getEl().unmask();
			}
		});
	},

	submitJmap : function(record) {

		var params = {};

		this.getEl().mask(t("Loading..."));

		if(record.data.id) {
			params.update = {};
			params.update[record.data.id] = {
				enabled: record.data.enabled,
				sort_order: record.data.sort_order ? record.data.sort_order : 0
			};

			go.Db.store("Module").save({
				enabled: record.data.enabled,
				sort_order: record.data.sort_order ? record.data.sort_order : 0
			}, record.data.id).then(() => {
				if(record.data.enabled && record.isModified("enabled")) {

					this.showRights(record.data.id, record.data.rights);

				}
				record.commit();
			}).catch((err) =>{
				Ext.MessageBox.alert(t("Error"), err.message);
				this.store.load();
			}).finally(() => {
				this.getEl().unmask();
			})

		} else
		{

			go.Jmap.request({
				method: "Module/install",
				params: {
					name: record.data.name,
					package: record.data.package
				},
				callback: function(options, success, response) {
					if(success) {
						record.set('enabled', true);
						record.set('id', response['list'][0].id);
						record.set('aclId', response['list'][0].aclId);
							this.showRights(record.data.id, record.data.rights);
							record.commit();
					} else
					{
						Ext.MessageBox.alert(t("Error"), response.message);
						this.store.load();
					}

					this.getEl().unmask();
				},
				scope: this
			});

		}


	},

	createJSON : function(params){

		var keys, JSON={}, currentJSONlevel;

		for(var key in params){

			keys = key.split('.');

			currentJSONlevel = JSON;

			for(var i=0;i<keys.length;i++){
				if(i===(keys.length-1)){

					// Change true to 1 for customfields checkboxes
					if(params[key] == true){
						params[key] = '1';
					}

					currentJSONlevel[keys[i]]= params[key];
				}else
				{
					currentJSONlevel[keys[i]]=currentJSONlevel[keys[i]] || {};
					currentJSONlevel=currentJSONlevel[keys[i]];
				}
			}

			currentJSONlevel = JSON;

		}

		return JSON;
	},

	deleteModule: function(record) {
		Ext.MessageBox.confirm(t("Delete"), t("All data will be lost! Are you sure you want to delete module '{item}'?").replace('{item}', record.data.name), function (cmd) {

			if (cmd != 'yes') {
				return;
			}



			if (record.data.isRefactored) {
				this.getEl().mask(t("Loading..."));
				go.Jmap.request({
					method: "Module/uninstall",
					params: {
						name: record.data.name,
						package: record.data.package
					},
					scope: this
				}).then(() => {
					record.set('enabled', false);
					record.set('id', null);
					record.commit();
				}).finally(() => {
					this.getEl().unmask();
				}).catch(e => {
					GO.errorDialog.show(e.message);
				})
			} else {
				GO.request({
					maskEl: this.getEl(),
					url: "modules/module/delete",
					params: {
						id: record.data.id
					},
					success: function (response, options) {
						record.set('enabled', false);
						record.set('id', null);
						record.commit();

						this.getEl().unmask();
					},
					scope: this
				});
			}

		}, this);

	}
});
