go.oauth.SystemSettingsPanel = Ext.extend(go.systemsettings.Panel, {
	hasPermission: function() {
		return go.User.isAdmin;
	},
	iconCls: 'ic-vpn-key',
	itemId: "oauth2", //makes it routable
	layout: "border",
	initComponent: function () {
		this.title = t("Oauth 2.0");
		
		this.items = [
			// {
			// 	cls: "text",
			// 	region: "north",
			// 	autoHeight: true,
			// 	html: t("API keys can be used for other services to connect to the API. A website feeding contact information for example.")
			// },
			new go.oauth.ClientGrid({
				region:"center"
			})
		];

		go.oauth.SystemSettingsPanel.superclass.initComponent.call(this);
	}
});
