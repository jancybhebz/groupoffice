/**
 * Relation field type.
 * 
 * Will lookup the relation by name from the entity declaration in Module.js.
 * 
 * When the relation is a has many you can also specify limit = 5 to limit the number of entities to resolve.
 * The total will be stored in record.json._meta[relName].total.
 */
Ext.data.Types.RELATION = {
	isRelation: true,
	prefetch: true, //used in EntityStoreProxy
	convert: function(v, data) {

		// var entity = this.entity, relName = this.name;
		// var relation = entity.relations[relName];
		// return go.Db.store(relation.store).data[data[relation.fk]];
		return v;
	},
	sortType: Ext.data.SortTypes.none
};