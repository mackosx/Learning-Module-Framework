class Passage {
	constructor(name, desc, top, left, weight) {
		this.name = name;
		this.desc = desc;
		this.top = top;
		this.left = left;
		this.weight = weight;
		this.isStart = false;
		if (app)
			this.id = app.passages.size();
		else
			throw new Error("Tried to instantiate passage before main app.");
	}
}

// create the ability to drag components
Vue.directive('draggable', {
	bind: function (el) {
		el.style.position = 'absolute';
		// index of selected passage
		let idx = el.attributes['data-i'].value;
		let startX, startY, initialMouseX, initialMouseY;

		function mousemove(e) {
			let dx = e.clientX - initialMouseX;
			let dy = e.clientY - initialMouseY;
			app.passages.objects[idx].top = startY + dy + 'px';
			app.passages.objects[idx].left = startX + dx + 'px';
			return false;
		}

		function mouseup() {
			document.removeEventListener('mousemove', mousemove);
			document.removeEventListener('mouseup', mouseup);
		}

		el.addEventListener('mousedown', function (e) {
			startX = el.offsetLeft;
			startY = el.offsetTop;
			initialMouseX = e.clientX;
			initialMouseY = e.clientY;
			document.addEventListener('mousemove', mousemove);
			document.addEventListener('mouseup', mouseup);
			return false;
		});
	}
});
// Component is passed array of Passages
Vue.component('passages', {
	props: ['passages', 'show'],
	//language=HTML
	template: `
        <div class="passage-area">
            <div v-for="(passage, index) in passages.objects" :key="passage.id">
                <passage :passage="passage" :show="show" :index="index" v-draggable></passage>
            </div>

        </div>
	`,
});
Vue.component('passage', {
	props: ['passage', 'show', 'index'],
	//language=HTML
	template: `
        <div class="passage-item" @mouseover="edit = true" @mouseleave="edit = false" :data-i="index"
             :style="{ top: passage.top, left: passage.left }"
             v-draggable>
            <div class="passage-start" v-show="passage.isStart == true"><i class="fa fa-rocket"></i></div>

            <div class="passage-content">
                <h2>{{ passage.name }}</h2>
                <p>{{ passage.desc }}</p>
            </div>
            <div class="passage-value">{{ passage.weight }}</div>
            <div class="passage-options" :style="{ display: edit == true ? 'block':'none' }">
                <div class="edit-passage-button" @click="show(index)">
                    <i class="fa fa-pencil-square-o"></i>
                </div>
                <div class="designate-start-button" @click="setStart">
                    <i class="fa fa-rocket"></i>
                </div>
            </div>

        </div>

	`,
	methods: {
		// Removes start designation from all other passages, then set this passage as start
		setStart(){
			for (let i = 0; i < app.passages.objects.length; i++) {
				app.passages.objects[i].isStart = false;
			}
			this.passage.isStart = true;
		}
	},
	data: function () {
		return {
			edit: false
		}
	}
});

Vue.component('editor', {
	//language=HTML
	template: `
        <div id="passage-editor-container" v-if="passages.size() > 0" v-show="display">
            <transition name="showEditor">
                <div id="passage-editor">
                    <h3>Edit Passage</h3>
                    <button class="close-editor" v-on:click="hide">&times;</button>
                    <label for="title-input">Title</label><br/>
                    <input type="text" id="title-input" maxlength="100" v-model="passages.objects[current].name"/>
                    <label for="text-input">Text</label><br/>
                    <textarea id="text-input" v-model="passages.objects[current].desc"
                              placeholder="Write your text here..."></textarea>
                    <label for="weight-input">Value</label><br/>
                    <input type="number" min="-100" max="100" id="weight-input"
                           v-model="passages.objects[current].weight">
                    <div id="parent-editor">
                        <label>Parents</label>
                        <!--Display current parents-->
                        <template v-for="(parent, pIndex) in passages.edges">
                            <template v-for="(child, cIndex) in parent">
                                <ul class="current-parents" v-if="child == current">
                                    <li class="remove-link" @click="removeParent(current, passages.objects[pIndex].id)">
                                        {{passages.objects[pIndex].name}}
                                        <i class="fa fa-times"></i>
                                    </li>
                                </ul>
                            </template>
                        </template>
                    </div>

                    <div id="child-editor">
                        <label for="child-select">Children</label>
                        <select id="child-select" @change="selectChild" v-model="passages.edges[current]" size="3"
                                multiple>
                            <option disabled value="">Select A Child</option>
                            <template v-for="passage in passages.objects" v-if="passage.id != current">
                                <option :value="passage.id">{{passage.name}}</option>
                            </template>
                        </select>
                        <ul class="current-children">
                            <template v-for="edge in passages.edges[current]">
                                <li class="remove-link" @click="removeChild(current, edge)">
                                    {{passages.objects[edge].name}}
                                    <i class="fa fa-times"></i>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </transition>

        </div>
	`,
	props: ['display', 'hide', 'current', 'passages'],
	methods: {
		selectParent(e){
			let parentIndex = parseInt(e.target.value);
			this.passages.addEdge(parentIndex, this.current);

		},
		selectChild(e){
			for (op in e.selectedOptions) {
				let childIndex = parseInt(op.value);
				this.passages.addEdge(this.current, childIndex);
				console.log(this.passages.edges[this.current]);
			}
		},
		removeChild(current, edge){
			this.passages.removeEdge(current, edge);
		},
		removeParent(current, child){
			this.passages.removeEdge(child, current);
		}

	}
});
// imports story from localized data
function checkStory(previousStory) {
	if (previousStory) {
		//parse json string
		const s = JSON.parse(previousStory.data);
		//create new digraph with object
		p = new Digraph();
		p.importFromJSON(s);
		return p;
	} else {
		return new Digraph();
	}
}

let app = new Vue({
	el: '#main',
	data: {
		passages: typeof previousStory !== 'undefined' ? checkStory(previousStory) : new Digraph(),
		title: 'New Story',
		desc: '',
		isEditing: false,
		currentPassageEdit: 0

	},
	methods: {
		addPassage(){
			let newPassage = new Passage('Untitled', '', '10px', '10px', 0);
			newPassage.name += ' ' + (newPassage.id + 1);
			this.passages.addObject(newPassage);
			this.showEditor(newPassage.id);
		},
		showEditor(passage){
			this.currentPassageEdit = passage;
			this.isEditing = true;
		},
		hideEditor(){
			//this.currentPassageEdit = -1;
			this.isEditing = false;
		}
	}

});
jQuery(document).ready(function ($) {
	let saved = false;
	$('#save-rpg').on('submit', function (e) {
		if (saved === true) {
			saved = false;
			return;
		}
		e.preventDefault();
		app.passages.compactToJSON(app.title, app.desc);
		saved = true;
		$(this).trigger('submit');
	})
});



