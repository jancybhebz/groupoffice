
import {NoteBookGrid} from "./NoteBookGrid.js";
import {AutocompleteField, jmapds} from "@intermesh/goui";
import {t} from "@intermesh/goui";
import {Config, createComponent} from "@intermesh/goui";
import {client} from "@intermesh/goui";

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

			const loadText = async () => {
				// record not available in store. Load it.
				if (this.input?.value == this.value) {
					const entityStore = jmapds("NoteBook");
					const nb = await entityStore.single(this.value);

					this.setInputValue(nb!.name);
				}
			}

			if (this.rendered) {
				await loadText();
			} else {
				this.on("render", () => {
					loadText();
				}, {once: true})
			}
		});
	}
}

export const notebookcombo = (config?: Config<NoteBookCombo>) => createComponent(new NoteBookCombo(), config);