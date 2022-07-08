import {column, datetimecolumn, Table} from "../../../../../../../views/Extjs3/goui/script/component/Table.js";
import {Store} from "../../../../../../../views/Extjs3/goui/script/data/Store.js";
import {t} from "../../../../../../../views/Extjs3/goui/script/Translate.js";
import {NoteDialog} from "./NoteDialog.js";
import {JmapStore, jmapstore} from "../../../../../../../views/Extjs3/goui/script/api/JmapStore.js";

export interface NoteGrid {
	store : JmapStore
}

export class NoteGrid extends Table {

	constructor() {

		const columns = [
			column({
				header: t("Name"),
				property: "name",
				sortable: true
			}),

			datetimecolumn({
				header: t("Created At"),
				property: "createdAt",
				sortable: true
			})
		];

		super(
			jmapstore({
				entity: "Note",
				sort: [{
					property: "name"
				}]
			}),
			columns
		);

		this.on("rowdblclick", (table, rowIndex, ev) => {
			const dlg = new NoteDialog();
			dlg.load(table.store.get(rowIndex).id);
			dlg.show();
		})
	}

}