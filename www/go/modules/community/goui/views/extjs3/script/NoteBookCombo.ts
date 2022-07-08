import {AutocompleteField} from "../../../../../../../views/Extjs3/goui/script/component/form/AutocompleteField.js";
import {NoteBookGrid} from "./NoteBookGrid.js";
import {Config} from "../../../../../../../views/Extjs3/goui/script/component/Observable.js";
import {t} from "../../../../../../../views/Extjs3/goui/script/Translate.js";
import {EntityStore} from "../../../../../../../views/Extjs3/goui/script/api/EntityStore.js";

export class NoteBookCombo extends AutocompleteField<NoteBookGrid> {
	constructor() {
		super(new NoteBookGrid());
		this.table.headers = false;

		this.label = t("Notebook");
		this.name = "noteBookId";
		this.valueProperty = "id";

		this.on("autocomplete", async (field, input) => {
			this.table.store.queryParams = {filter: {text: input}};
			await this.table.store.load();
		});

		this.on("setvalue", async (field, newValue, oldValue) => {

			if(this.input?.value == this.value) {
				// record not available in store. Load it.
				const entityStore = new EntityStore("NoteBook");
				const nb = await entityStore.single(this.value);

				this.setInputValue(nb.name);
			}
		});
	}
}

export const notebookcombo = (config?: Config<NoteBookCombo>) => Object.assign(new NoteBookCombo(), config);