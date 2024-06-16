
/* global go, Ext */

go.modules.community.addressbook.SettingsProfilePanel = Ext.extend(Ext.Panel, {
	title: t("Profile"),
	iconCls: 'ic-person',
	labelWidth: 125,
	layout: "fit",
	autoScroll: true,
	index: 1,
	initComponent: function () {

		//The account dialog is an go.form.Dialog that loads the current User as entity.
		this.items = [{
				xtype: "formcontainer",
				name: "profile",
				items: [
					{
						xtype: 'fieldset',
						items: [
							this.nameField = new go.modules.community.addressbook.NameField({
								name: "name",
								flex: 1,
								allowBlank: true
							}),
							this.jobTitle = new Ext.form.TextField({
								xtype: "textfield",
								name: "jobTitle",
								fieldLabel: t("Job title"),
								anchor: "100%"
							}),
							this.departmentField = new Ext.form.TextField({
								xtype: "textfield",
								name: "department",
								fieldLabel: t("Department"),
								anchor: "100%"
							}),
							this.genderField = new go.form.RadioGroup({
								xtype: 'radiogroup',
								fieldLabel: t("Gender"),
								name: "gender",
								value: null,
								items: [
									{boxLabel: t("Unknown"), inputValue: null},
									{boxLabel: t("Male"), inputValue: 'M'},
									{boxLabel: t("Female"), inputValue: 'F'}
								]
							}),
							this.organizationsField = new go.form.Chips({
								anchor: '-20',
								xtype: "chips",
								entityStore: "Contact",
								displayField: "name",
								valueField: 'id',
								allowNew: {
									isOrganization: true,
									addressBookId: go.Modules.get("core", "core").settings.userAddressBookId
								},
								comboStoreConfig: {
									sortInfo: {
										field: 'name',
										direction: 'ASC'
									},
									filters:  {
										defaults: {
											isOrganization: true
										}
									}
								},
								name: "organizationIds",
								fieldLabel: t("Organizations")
							}),
						]},
					{
						xtype: 'fieldset',
						title: t("Communication"),
						items: [
							new go.modules.community.addressbook.PhoneNumbersField(),
							new go.modules.community.addressbook.AddressesField()
						]
					},
					{
						xtype: "fieldset",
						title: t("Other"),
						layout: 'column',
						defaults: {
							columnWidth: .5,
							anchor: "-20"
						},
						items: [
							new go.modules.community.addressbook.DatesField(),
							new go.modules.community.addressbook.UrlsField()
						]
					}
				]
			}
		];

		this.addCustomFields(this.items[0].items);

		go.modules.community.addressbook.SettingsProfilePanel.superclass.initComponent.call(this);
	},
	addPanel: function(pnl) {
		const fs = pnl.items.itemAt(0);
		fs.title = pnl.title;
		fs.collapsible = true;
		fs.isTab = false;
		this.items[0].items.push(fs)
	},
	entityStore: 'Contact',
	addCustomFields: go.modules.community.addressbook.ContactDialog.prototype.addCustomFields,
	getCustomFieldSets: go.modules.community.addressbook.ContactDialog.prototype.getCustomFieldSets
});


