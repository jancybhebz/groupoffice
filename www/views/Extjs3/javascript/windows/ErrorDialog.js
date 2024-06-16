GO.ErrorDialog = function(config) {
	config = config || {};

	Ext.apply(config, {
		width: dp(872),
		closeAction : 'hide',
		plain : true,
		height: dp(460),
		layout: 'fit',
		border : false,
		closable : true,
		title : t("Error"),
		modal : true, 
		items : [
		this.messagePanel = new Ext.Panel({							
			cls : 'go-error-dialog',		
			autoScroll:true,
			html : ''
		})],
		buttonAlign:"center",
		buttons: [{
			text: t("Copy"),
			handler: function() {
				go.util.copyTextToClipboard(
					this.messagePanel.body.dom.innerHTML
						.replace('/<br>/i', "\n")
						.replace('/<br />/i', "\n")
				);
			},
			scope: this
		}, {
			text: t("Close"),
			cls: 'primary',
			handler: function() {
				this.hide();
			},
			scope: this
		}]
	});

	GO.ErrorDialog.superclass.constructor.call(this, config);
}

Ext.extend(GO.ErrorDialog, GO.Window, {

	show : function(error, title, html = false) {

		console.error(error);
		if(Ext.isString(error)) {
			console.trace('errordialog');
		}
		
		if(!title) {
			title = t("Error");

			var now = new Date();

			title += ' - ' + now.format("Y-m-d G:i");
		}
		this.setTitle(title);

		if (!this.rendered)
			this.render(Ext.getBody());

		if(!error)
			error = "No error message given";
		else if(error.message) {
			error = error.message;
		}
		
		this.setHeight(dp(180));
		if(!html) {
			error = Ext.util.Format.nl2br(Ext.util.Format.htmlEncode(error));
		}
		this.messagePanel.body.update(error);

		GO.ErrorDialog.superclass.show.call(this);
		
		if(this.messagePanel.body.isScrollable()) {
			var newHeight = this.messagePanel.body.dom.scrollHeight + dp(180); // add 30 for horizontal scrollbar
							
			this.setHeight(newHeight);
			this.autoSize();

		}
		this.center();
	}
});
GO.errorDialog = new GO.ErrorDialog();
