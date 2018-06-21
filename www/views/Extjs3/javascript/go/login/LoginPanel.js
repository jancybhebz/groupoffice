go.login.LoginPanel = Ext.extend(Ext.Container, {
	id: "login",
	renderTo: document.body,
	initComponent: function () {

		this.languageContainer = new Ext.Container({
			id: 'go-select-language',
			//renderTo: 'go-select-language',
			layout: 'form',
			items: [				
				this.langCombo = new go.login.LanguageCombobox({
					listeners: {
						select: function (cmb) {
							if (cmb.getValue() != '') {
								document.location = BaseHref + 'index.php?SET_LANGUAGE=' + cmb.getValue();
							}
						},
						scope: this
					}
				})
			]
		});

        var htmlText = 'Powered by ' + t('product_name');
        if (t('product_name') == 'Group-Office') {
            htmlText = htmlText + ' <a target="_blank" href="http://www.group-office.com">http://www.group-office.com</a>';
        }

        this.items = [
					this.logoComp = new Ext.BoxComponent({cls: "go-app-logo"}),
            this.languageContainer,
            {
                xtype: 'box',
                id: 'go-powered-by',
                html: htmlText
            },{
                xtype: 'box',
                id: "bg"
            }
        ];
		
		
		go.login.LoginPanel.superclass.initComponent.call(this);

		this.on('render', function () {

			//todo, this dialog should be part of this conponent
			GO.loginDialog = new go.login.LoginDialog();
			GO.loginDialog.show();

			var me = this;
			setTimeout(function () {
				if (GO.settings.config.debug) {
					go.notifier.msg({
                        title: t("Warning! Debug mode enabled"), icon: 'warning', description: t("Use $config['debug']=true; only with development and problem solving. It slows " + t('product_name') + " down."), time: 4000					});
				}

				if (GO.settings.config.login_message) {
					var msg = go.notifier.msg({
						description: GO.settings.config.login_message
					});
					
					me.on("destroy", function() {
						go.notifier.remove(msg);
					});
					
				}
			}, 1000); // 1 second delay for groupoffice loading

		}, this);

	}
});
