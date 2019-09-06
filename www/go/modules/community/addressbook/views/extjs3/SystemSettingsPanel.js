
/* global go, Ext */

go.modules.community.addressbook.SystemSettingsPanel = Ext.extend(go.systemsettings.Panel, {

	title: t("Address book"),
	iconCls: 'ic-contacts',
	labelWidth: 125,
	initComponent: function () {

		//The account dialog is an go.form.Dialog that loads the current User as entity.
		this.items = [{
			xtype: "fieldset",
			items: [{
				hideLabel: true,
				xtype: "checkbox",
				boxLabel: t("Automatically link e-mail to contacts"),
				name: "autoLinkEmail",
				disabled: !GO.savemailas,
				hint: t("Warning: this will copy e-mails to the Group-Office storage and will therefore increase disk space usage. The e-mail will be visible to all people that can view the contact too.")
			}]
		}];

		go.modules.community.addressbook.SystemSettingsPanel.superclass.initComponent.call(this);
	}

});

