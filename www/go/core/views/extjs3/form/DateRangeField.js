go.form.DateRangeField = Ext.extend(Ext.Button, {

	value: null,
	lastValue: null,

	text: t("Select date range..."),

	iconCls: 'ic-schedule',

	isFormField: true,

	initComponent: function() {

		var me = this;

		this.startDatePicker = new Ext.DatePicker({
			showToday: false
		});

		this.endDatePicker = new Ext.DatePicker({
			showToday: false
		});
		this.endDatePicker.on("select", this.onEndDateSelect, this);

		this.menu = new Ext.menu.Menu({
			items: [{
				xtype: "container",
				layout: "column",
				defaults: {
					columnWidth: .5
				},
				items: [
					this.startDatePicker,
					this.endDatePicker
				]
			}],
			doFocus: function () {
				me.startDatePicker.focus();
			}
		});

		this.supr().initComponent.call(this);
	},

	getName: function () {
		return this.name;
	},

	isDirty: function () {

	},

	reset: function () {
		this.setValue(null);
	},

	setValue: function (v) {
		this.lastValue = this.getValue();
		this.updateBtnText();
	},

	getValue: function () {
		return this.startDatePicker.getValue().format("Y-m-d") +
			".." +
			this.endDatePicker.getValue().format('Y-m-d');
	},

	getRawValue : function() {
		return this.getText();
	},

	markInvalid: function (msg) {

	},

	clearInvalid: function () {

	},

	isValid : function(preventMark){

		return true;
	},

	validate: function () {
		return true;
	},

	focus: function () {

	},

	updateBtnText: function() {
		var txt = go.util.Format.date(this.startDatePicker.getValue()) + ' - ' + go.util.Format.date(this.endDatePicker.getValue());
		this.setText(txt);
	},
	onEndDateSelect : function(dp, date) {
		this.updateBtnText();

		this.fireEvent("change", this, this.getValue(), this.lastValue);
	}
});

Ext.reg("godaterangefield", go.form.DateRangeField);
