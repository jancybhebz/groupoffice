Ext.ns("go.customfields.type");

go.customfields.type.Select = Ext.extend(go.customfields.type.Text, {
	
	name : "Select",
	
	label: t("Select"),
	
	iconCls: "ic-list",	
	
	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.customfields.FieldDialog}
	 */
	getDialog : function() {
		return new go.customfields.type.SelectDialog();
	},
	
	/**
	 * Render's the custom field value for the detail views
	 * 
	 * @param {mixed} value
	 * @param {object} data Complete entity
	 * @param {object} customfield Field entity from custom fields
	 * @returns {unresolved}
	 */
	renderDetailView: function (value, data, customfield) {		
		var text = this.findRecursive(value, customfield.dataType.options);
		return text ? text.substr(3) : null;
	},

	findRecursive: function (value, options, text) {
		if(!text) {
			text = "";
		}
		var o;
		for(var i = 0, l = options.length; i < l; i++) {
			o = options[i];
			if(o.id == value) {

				text += " > " + o.text;

				return text;
			}

			if(o.children) {
				var nested = this.findRecursive(value, o.children, text + " > " + o.text);
				if(nested) {
					return nested;
				}
			}
		}

		return null;
	},
	
	/**
	 * Returns config object to create the form field
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig: function (customfield, config) {
		let c = go.customfields.type.Select.superclass.createFormFieldConfig.call(this, customfield, config);

		c.xtype = "treeselectfield";
		c.customfield = customfield;
		return c;
	},

	getFieldType: function () {
		return "int";
	},
	
	/**
	 * Get the field definition for creating Ext.data.Store's
	 * 
	 * Also the customFieldType (this) and customField (Entity Field) are added
	 *
	 * @returns {Object}
	 */
	getFieldDefinition : function(field) {
		
		let c = go.customfields.type.Select.superclass.getFieldDefinition.call(this, field);
		
		c.convert = function(v, record) {
			return this.customFieldType.renderDetailView(v, record.data, this.customField);
		};		
		
		return c;
	},
	
	getFilter : function(field) {
			
		return {
			name: field.databaseName,
			type: "go.customfields.type.TreeSelectField",
			multiple: true,
			wildcards: true,
			title: field.name,
			customfield: field
		};
	},
	/**
	 * Get grid column definition
	 *
	 * @param {type} field
	 * @returns {TextAnonym$0.getColumn.TextAnonym$6}
	 */
	getColumn : function(field) {
		const def = this.getFieldDefinition(field);
		let c = {
			dataIndex: def.name,
			header: def.customField.name,
			hidden: def.customField.hiddenInGrid,
			id: "custom-field-" + encodeURIComponent(def.customField.databaseName),
			sortable: true,
			hideable: true,
			draggable: true,
			xtype: this.getColumnXType()
		};

		c.renderer = function(val, metadata, record, rowIndex, i, store) {
			if(!go.util.empty(val)) {
				const selectedOption = field.dataType.options.find(elm => elm.text === val);
				if(selectedOption && selectedOption.renderMode === "cell") {
					let inlineStyle = "";
					if (selectedOption.foregroundColor) {
						inlineStyle += "color: #" + selectedOption.foregroundColor + ";";
					}
					if (selectedOption.backgroundColor) {
						//inlineStyle += "background-color: #" + selectedOption.backgroundColor + ";";
						val = '<div class="status" style="background-color: #' + selectedOption.backgroundColor + '">' + val + '</div>';
					}

					let cellStyle = metadata.style || '';
					if (!go.util.empty(inlineStyle)) {
						cellStyle += inlineStyle;
					}
					metadata.style = cellStyle;
				}
			}
			return val;
		};
		c.rowRenderer = function(val) {
			if (!go.util.empty(val)) {
				const selectedOption = field.dataType.options.find(elm => elm.text === val);
				if(!selectedOption || selectedOption.renderMode !== "row") {
					return false;
				}

				let inlineStyle =  "";
				if (selectedOption.foregroundColor) {
					inlineStyle += "color: #" + selectedOption.foregroundColor + ";";
				}
				if(selectedOption.backgroundColor) {
					inlineStyle += "background-color: #" + selectedOption.backgroundColor + ";";
				}
				return inlineStyle;
			}
			return false;
		};

		return c;
	},
});


// go.customfields.CustomFields.registerType(new go.customfields.type.Select());
