Ext.ns('go.modules.community.multi_instance');

go.Modules.register("community", 'multi_instance', {
	mainPanel: "go.modules.community.multi_instance.MainPanel",
	title: t("Multi instance"),
	entities: [{
		name:"Instance",
		filters: [
			{
				wildcards: false,
				name: 'text',
				type: "string",
				multiple: false,
				title: t("Query")
			},
			{
				title: t("Modified at"),
				name: 'modifiedat',
				multiple: false,
				type: 'date'
			}, {
				title: t("Modified by"),
				name: 'modifiedBy',
				multiple: true,
				type: 'string'
			}, {
				title: t("Created at"),
				name: 'createdat',
				multiple: false,
				type: 'date'
			}, {
				title: t("Created by"),
				name: 'createdby',
				multiple: true,
				type: 'string'
			},
			{
				title: t("E-mail"),
				name: 'adminEmail',
				multiple: true,
				type: 'string'
			},{
				title: t("Admin name"),
				name: 'adminDisplayName',
				multiple: true,
				type: 'string'
			},{
				title: t("Is trial"),
				name: 'isTrial',
				type: "select",
				options: [{
					value: 1,
					title: t("Yes")
				},{
					value: 0,
					title: t("No")
				}]
			}
			]
	}],
	initModule: function () {	
		
	}
});


