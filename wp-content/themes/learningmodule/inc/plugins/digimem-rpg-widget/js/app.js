let bus = new Vue();

class Passage {
	constructor(name, desc, top, left, weight, id) {
		this.name = name;
		this.desc = desc;
		this.top = top;
		this.left = left;
		this.weight = weight;
		this.isStart = false;
		this.id = id;
		this.isEnd = false;
	}
}

// create the ability to drag components
Vue.directive('draggable', {
	bind: function (el, binding, vnode) {
		el.style.position = 'absolute';
		// index of selected passage
		let startX, startY, initialMouseX, initialMouseY;
		let id = el.attributes['data-i'].value;
		let index = 0;

		function mousemove(e) {
			e.preventDefault();
			let dx = e.clientX - initialMouseX;
			let dy = e.clientY - initialMouseY;

			let validArea = jQuery('.passage-area')[0];
			let psg = jQuery('.passage-item');

			if (isValidTop(startY + dy, validArea.clientHeight, psg.height()))
				app.passages.vertices[index].data.top = startY + dy + 'px';
			if (isValidLeft(startX + dx, validArea.clientWidth, psg.width()))
				app.passages.vertices[index].data.left = startX + dx + 'px';


			return false;
		}

		function isValidTop(top, limit, height) {

			return top > 0 && top < (limit - height);

		}

		function isValidLeft(left, limit, width) {
			return left > 0 && left < (limit - width);
		}

		function mouseup() {
			document.removeEventListener('mousemove', mousemove);
			document.removeEventListener('mouseup', mouseup);
		}

		el.addEventListener('mousedown', function (e) {
			e.preventDefault();
			id = el.attributes['data-i'].value;
			index = app.passages.getIndexOf(id);
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
	props: ['passages', 'show', 'zoom'],
	//language=HTML
	template: `
        <div class="passage-area">
            <template v-for="(passage, index) in passages.vertices">
                <passage :passage="passage.data" :zoom="zoom" :show="show" :index="passage.data.id"
                         :key="passage.data.id"
                         v-draggable></passage>
            </template>
        </div>
	`,
	watch: {
		passages: function () {
			// executes whenever passages is updated, after DOM is updated
		}
	}
});
//Story Passage component
Vue.component('passage', {
	props: ['passage', 'show', 'index', 'zoom'],
	//language=HTML
	template: `
        <div class="passage-item" :class="{ dragged: moving && edit}" @mousedown="mouseDown" @mouseup="moving = false"
             @mouseleave="moving = edit = false" @mouseenter="edit = true" :data-i="index"
             :style="{ top: passage.top, left: passage.left }"
             v-draggable>
            <div class="passage-identifier" v-show="passage.isStart == true">
                <i class="fa fa-rocket" aria-label="The starting passage"></i>
            </div>
            <div class="passage-identifier" v-if="passage.isEnd == true">
                <i class="fa fa-stop-circle-o" aria-label="The ending passage"></i>
            </div>

            <div class="passage-content">
                <h2>{{ passage.name }}</h2>
                <p v-show="!zoom">{{ passage.desc }}</p>
            </div>

            <div class="passage-value">
                {{ passage.weight }}
            </div>
            <div class="passage-options" :style="{ display: edit == true && moving == false ? 'block':'none' }">
                <div class="edit-passage-button" @click="show(index)">
                    <i class="fa fa-pencil" title="Edit" aria-label="Edit"></i>
                </div>
                <div class="designate-start-button" @click="setStart">
                    <i class="fa fa-rocket" title="Set as Start" aria-label="Set as Start"></i>
                </div>
                <div class="designate-end-button" @click="setEnd">
                    <i class="fa fa-stop-circle-o" title="Set as End" aria-label="Set as End"></i>
                </div>
                <div class="delete-passage-button" @click="remove">
                    <i class="fa fa-trash" title="Delete" aria-label="Delete"></i>
                </div>
            </div>

        </div>

	`,
	methods: {
		// Removes start designation from all other passages, then set this passage as start
		setStart() {
			for (let i = 0; i < app.passages.vertices.length; i++) {
				app.passages.vertices[i].data.isStart = false;
			}
			this.passage.isEnd = false;
			this.passage.isStart = true;
		},
		setEnd() {
			for (let i = 0; i < app.passages.vertices.length; i++) {
				app.passages.vertices[i].data.isEnd = false;
			}
			this.passage.isStart = false;
			this.passage.isEnd = true;
		},
		// Deletes this passage from the list of vertices
		remove(e) {
			//e.stopPropagation();
			app.passages.removeVertex(this.passage.id);
			if (app.passages.size())
				this.current = 0;
			else
				this.current = -1;
		},
		mouseDown(e) {
			this.moving = true;
			jQuery(e.target).trigger('click');
		},


	},
	data: function () {
		return {
			edit: false,
			moving: false
		}
	},
	mounted: function () {
		// when rendered
		bus.$emit('passageCreated', this.$el, this.passage.id);
	},

});

Vue.component('editor', {
	//language=HTML
	template: `
        <div id="passage-editor-container" v-if="current < passages.size()"
             v-show="display">
            <transition name="showEditor">
                <div id="passage-editor">
                    <h3>Edit Passage</h3>
                    <button class="close-editor" @click="hide"><i class="fa fa-times"></i></button>
                    <label for="title-input">Title</label><br/>
                    <input type="text" id="title-input" maxlength="100" v-model="passages.vertices[current].data.name"/>
                    <label for="text-input">Text</label><br/>
                    <textarea id="text-input" v-model="passages.vertices[current].data.desc"
                              placeholder="Write your text here..."></textarea>
                    <label for="weight-input">Value</label><br/>
                    <input type="number" min="-100" max="100" id="weight-input"
                           v-model="passages.vertices[current].data.weight">
                    <div id="parent-editor">
                        <label>Parents</label>
                        <!--Display current parents-->
                        <template v-for="(parent, pIndex) in passages.vertices">
                            <template v-for="(child, cIndex) in parent.edges">
                                <ul class="current-parents" v-if="child.id == passages.vertices[current].data.id">
                                    <li class="remove-link">
                                        {{passages.vertices[pIndex].data.name}}
                                        <i class="fa fa-times"></i>
                                    </li>
                                </ul>
                            </template>
                        </template>
                    </div>

                    <div id="child-editor">
                        <label for="child-select">Children</label>
                        <select id="child-select" @change="selectChild">
                            <option value="">Select A Child</option>
                            <template v-for="passage in passages.vertices"
                                      v-if="passage.data.id != passages.vertices[current].data.id	">
                                <option :value="passage.data.id">{{passage.data.name}}</option>
                            </template>
                        </select>
                        <ul class="current-children">
                            <template v-for="child in passages.vertices[current].edges">
                                <li class="remove-link" @click="removeChild(child.id)">
                                    {{passages.vertices[passages.getIndexOf(child.id)].data.name}}
                                    <i class="fa fa-times"></i>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </transition>

        </div>
	`,
	props: ['display', 'hide', 'current', 'passages', 'passageElements'],
	methods: {
		selectParent(e) {
			let parentIndex = parseInt(e.target.value);
			this.passages.addEdge(parentIndex, this.current);

		},
		selectChild(e) {
			// add selected edges
			if(e.target.value != ''){
				let childId = parseInt(e.target.value);
				let child, parent;
				for (let i = 0; i < this.passageElements.length; i++) {
					let curr = parseInt(this.passageElements[i].id);
					if (curr === childId){
						child = this.passageElements[i].el;
					} else if(curr === parseInt(this.current)){
						parent = this.passageElements[i].el;
					}
				}
				// add the element as an edge
				this.passages.vertices[this.current].addChild(parent, child, childId);
			}
		},
		removeChild(childId) {
			this.passages.vertices[this.current].removeChild(childId);

		},
		removeParent(child, parent) {
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
		let data = p.importFromJSON(s);
		return {
			title: data.title,
			desc: data.desc,
			data: p
		};
	} else {
		return {
			title: 'New Story',
			desc: '',
			data: new Digraph()
		};
	}
}
let previous = checkStory(previousStory);

let app = new Vue({
	el: '#main',
	data: {
		zoomLevel: false,
		passages: previous.data,
		title: previous.title,
		desc: previous.desc,
		isEditing: false,
		currentPassageEdit: 0,
		passageElements: []

	},
	computed: {
		zoomClass: function () {
			return {
				'fa-search-plus': this.zoomLevel,
				'fa-search-minus': !this.zoomLevel
			}
		}

	},
	methods: {
		addPassage() {
			// Set the id of the new passage to one more than the biggest id
			const len = this.passages.vertices.length;
			let id = 0;
			if (this.passages.vertices[len - 1] !== undefined)
				id = this.passages.vertices[len - 1].data.id + 1;
			let newPassage = new Passage('Untitled ' + (id + 1), '', '10px', '10px', 0, id);
			this.passages.addVertex(newPassage);
			this.showEditor(newPassage.id);
		},
		showEditor(id) {
			this.currentPassageEdit = this.passages.getIndexOf(id);
			this.isEditing = true;
		},
		hideEditor() {
			//this.currentPassageEdit = -1;
			this.isEditing = false;
		},
		zoom() {
			this.zoomLevel = !this.zoomLevel;
			let psg = jQuery('.passage-item');
			let currWidth = psg.width();
			let currHeight = psg.height();
			if (this.zoomLevel) {
				psg.css('width', currWidth / 2);
				psg.css('height', currHeight / 2);
				this.drawArrows();
			}
			else {
				psg.css('width', currWidth * 2);
				psg.css('height', currHeight * 2);
			}
		},
		connect(from, to) {
			let conn;
			conn = jsPlumb.connect({
				source: from,
				target: to,
				anchor: ["Perimeter", {shape: "Square"}],
				overlays: [
					["Arrow", {location: 1, width: 15, height: 15, id: 'arrow'}]
				],
				endpoints: ['Blank', 'Blank'],
				connector: ['Bezier', {curviness: 20}],
				detachable: false
			});
			return conn;
		},
		drawArrows() {
			// resets endpoints and draws arrows from scratch
			jsPlumb.ready(function () {
				jsPlumb.deleteEveryEndpoint();
				jsPlumb.draggable(jQuery('.passage-item'));
				jsPlumb.Defaults.MaxConnections = 5;
			});
			for (let i = 0; i < this.passageElements.length; i++) {
				let el = this.passageElements[i];
				let index = this.passages.getIndexOf(el.id);
				for (let j = 0; j < this.passages.vertices[index].edges.length; j++) {
					let edgeId = this.passages.vertices[index].edges[j].id;
					for (let k = 0; k < this.passageElements.length; k++) {
						let childEl = this.passageElements[k];
						if (edgeId == childEl.id) {
							jsPlumb.ready(() => {
								this.passages.vertices[index].edges[j].connection = this.connect(el.el, childEl.el);
							});

						}
					}
				}
			}
		}

	},
	created: function () {
		let p = this;
		// create array of refs to html objects corresponding to passages
		bus.$on('passageCreated', (div, id) => {
			this.passageElements.push({
				el: div,
				id: id
			});
			if (this.passageElements.length === this.passages.vertices.length) {
				this.drawArrows();
			}
		});
	},

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


