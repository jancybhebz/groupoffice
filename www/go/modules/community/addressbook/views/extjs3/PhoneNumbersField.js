(function () {
	var store = new Ext.data.ArrayStore({
		xtype: "arraystore",
		idIndex: 0,
		fields: [
			'value',
			'display'
		],
		data: go.modules.community.addressbook.typeStoreData('phoneTypes')
	});

	go.modules.community.addressbook.PhoneNumbersField = Ext.extend(go.form.FormGroup, {
		xtype: "formgroup",
		name: "phoneNumbers",
		addButtonText: t("Add phone number"),
		addButtonIconCls: 'ic-phone',
		itemCfg: {
			items: [{
				anchor: "100%",
				layout: "form",
				xtype: "container",
				cls: "go-hbox condensed-form",
				items: [{
					fieldLabel: t("Type"),
					xtype: 'combo',
					name: 'type',
					mode: 'local',
					editable: false,
					triggerAction: 'all',
					store: store,
					valueField: 'value',
					displayField: 'display',
					width: dp(140),
					mobile: {
						width: dp(100)
					},
					value: "work"
				}, {
					fieldLabel: t("Number"),
					flex: 1,
					xtype: "textfield",
					allowBlank: false,
					name: "number",
					setFocus: true
				}]
			}]
		}
	});
})();