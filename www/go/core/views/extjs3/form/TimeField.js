go.form.TimeField = Ext.extend(Ext.form.TextField, {
	width: dp(72),
	defaultAutoCreate : {tag: 'input', type: 'time', size: '20', autocomplete: 'off'},

	initComponent: function() {
		go.form.TimeField.superclass.initComponent.call(this);

		if(Ext.isSafari) {
			this.on("blur", function() {
				var v = this.getRawValue();
				if(v.indexOf(':') === -1) {
					this.setValue(v + ':00');
				}
			}, this);
		}
	},

	setMinutes: function(minutes) {
		var duration = go.util.Format.duration(minutes);
		if(duration.length < 5) {
			duration = '0'+duration;
		}
		this.setRawValue(duration);
	},

	getMinutes: function() {
		return go.util.Format.minutes(this.getRawValue());
	},

	setValue : function(v) {
		if(!go.util.empty(v)) {
			var parts = v.split(":");
			if(parts.length == 3) {
				parts.pop(); //pop seconds
			}
			v = parts.join(":");
		} 
		go.form.TimeField.superclass.setValue.call(this, v);
	},

	getValue: function() {
		var v = this.getRawValue();
		if(!v) {
			return;
		}
		if(v.length > 5) {
			return v;
		}
		return v + ':00'; // add some second to match mysql time field 
	}
});

Ext.reg('nativetimefield', go.form.TimeField);