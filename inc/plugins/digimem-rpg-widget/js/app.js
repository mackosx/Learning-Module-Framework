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
		let index = el.attributes['data-i'].value;

		function mousemove(e) {
			e.preventDefault();
			let dx = e.clientX - initialMouseX;
			let dy = e.clientY - initialMouseY;

			let validArea = jQuery('.passage-area')[0];
			let psg = jQuery('.passage-item');

			app.passages.vertices[index].data.top = startY + dy + 'px';
			app.passages.vertices[index].data.left = startX + dx + 'px';

			return false;
		}
		function mouseup() {
			document.removeEventListener('mousemove', mousemove);
			document.removeEventListener('mouseup', mouseup);
		}

		el.addEventListener('mousedown', function (e) {
			e.preventDefault();
			index = el.attributes['data-i'].value;
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
                <passage :passages="passages" :passage="passage.data" :zoom="zoom" :show="show" :index="passage.data.id"
                         :key="passage.data.id"
                ></passage>
            </template>
        </div>
	`
});
//Story Passage component
Vue.component('passage', {
	props: ['passage', 'show', 'index', 'zoom', 'passages'],
	//language=HTML
	template: `
        <div class="passage-item connect" :class="{ dragged: moving && edit}" @mousedown="mouseDown"
             @mouseup="moving = false"
             @mouseleave="moving = edit = false" @mouseenter="edit = true" :data-i="passages.getIndexOf(index)"
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
                {{ passage.weight > 0 ? '+' + passage.weight : passage.weight }}
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
			moving: false,
		}
	},
	mounted() {
		// when rendered
		jsPlumb.draggable(jQuery(this.$el), {
			containment: true
		});
		this.passage.element = this.$el.id;
		// jsPlumb.ready(() => {
		// 	let incoming = this.$el;
		// 	jsPlumb.makeTarget(incoming, {
		// 		allowLoopback: false
		// 	});
		//
		// 	let outgoing = jQuery(this.$el).children('.passage-identifier.outgoing').get(0);
		// 	jsPlumb.makeSource(outgoing);
		// 	console.log(incoming, outgoing);
		// });
	},

});

Vue.component('editor', {
	//language=HTML
	template: `
        <div id="passage-editor-container" v-if="current < passages.size() && ~current"
             v-show="display">
            <transition name="showEditor">
                <div id="passage-editor">
                    <h3>Edit Passage</h3>
                    <button class="close-editor" @click="hide"><i class="fa fa-times"></i></button>
                    <label for="title-input">Title</label><br/>
                    <input type="text" id="title-input" maxlength="100" v-model="title"/>
                    <label for="text-input">Text</label><br/>
                    <textarea id="text-input" v-model="text"
                              placeholder="Write your text here..."></textarea>
                    <label for="weight-input">Value</label><br/>
                    <input type="number" min="-99" max="99" id="weight-input"
                           v-model="value">
                    <div id="parent-editor">
                        <label>Passages leading to this one</label>
                        <!--Display current parents-->
                        <template v-for="(parent, pIndex) in passages.vertices">
                            <template v-for="(child, cIndex) in parent.edges">
                                <ul class="current-parents" v-if="child.id == id">
                                    <li class="remove-link">
                                        {{passages.vertices[pIndex].data.name}}
                                        <i class="fa fa-times"></i>
                                    </li>
                                </ul>
                            </template>
                        </template>
                    </div>

                    <div id="child-editor">
                        <label for="child-select">Choices</label>
                        <select id="child-select" @change="selectChild">
                            <option value="" selected>Select A Child</option>
                            <template v-for="passage in passages.vertices"
                                v-if="passage.data.id != id">
                                <option :value="passage.data.id">{{passage.data.name}}</option>
                            </template>
                        </select>
                        <ul class="current-children">
                            <template v-for="child in edges">
                                <li class="remove-link" @click="removeChild(child.id)">
                                    {{passages.vertices[passages.getIndexOf(child.id)] ?
                                    passages.vertices[passages.getIndexOf(child.id)].data.name : ''}}
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
	// Properties that might be undefined when deleting
	computed: {
		edges: {
			get () {
				if (this.defined()) return this.passages.vertices[this.current].edges;
			}
		},
		id: {
			get () {
				if (this.defined()) return this.passages.vertices[this.current].data.id
			},
		},
		title: {
			get () {
				if (this.defined()) return this.passages.vertices[this.current].data.name;
			},
			set (val) {
				this.passages.vertices[this.current].data.name = val;
			}
		},
		text: {
			get () {
				if (this.defined()) return this.passages.vertices[this.current].data.desc;
			},
			set (val) {
				this.passages.vertices[this.current].data.desc = val;
			}
		},
		value: {
			get () {
				if (this.defined()) {
						return this.passages.vertices[this.current].data.weight;
				}
			},
			set (val) {
				this.passages.vertices[this.current].data.weight = val;
			}
		}
	},
	methods: {
		selectChild(e) {
			// add selected edges
			if (e.target.value != '') {
				let id = parseInt(e.target.value);
				let index = this.passages.getIndexOf(id);
				let childEl = this.passages.vertices[index].data.element;
				let parentEl = this.passages.vertices[this.current].data.element;// not defined
				// add the element as an edge
				this.passages.vertices[this.current].addChild(parentEl, childEl, id);
				jQuery('#child-select').val('');
			}
		},
		removeChild(childId) {
			this.passages.vertices[this.current].removeChild(childId);
		},
		defined() {
			return this.passages.vertices[this.current] != 'undefined';
		}
	}
});

// imports story from localized data
function checkStory(previousStory) {
	if (previousStory && previousStory != '') {
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

let previous = typeof previousStory !== 'undefined' ? checkStory(previousStory) : '';

let app = new Vue({
		el: '#main',
		data: {
			zoomLevel: false,
			passages: previous.data,
			title: previous.title,
			desc: previous.desc,
			isEditing: false,
			currentPassageEdit: 0,

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
				let newPassage = new Passage('Untitled ' + (id + 1), '', '80px', '80px', 0, id);
				this.passages.addVertex(newPassage);
				//this.showEditor(newPassage.id);
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
				});
				return conn;
			},
			drawArrows() {
				// resets endpoints and draws arrows from scratch based on edge array
				jsPlumb.ready(function () {
					jsPlumb.deleteEveryEndpoint();
				});
				for (let i = 0; i < this.passages.vertices.length; i++) {
					let el = this.passages.vertices[i].data.element;
					for (let j = 0; j < this.passages.vertices[i].edges.length; j++) {
						let edgeId = this.passages.vertices[i].edges[j].id;
						let connectingEdgeIndex = this.passages.getIndexOf(edgeId);
						let childEl = this.passages.vertices[connectingEdgeIndex].data.element;
						if (edgeId === this.passages.vertices[connectingEdgeIndex].data.id) {
							jsPlumb.ready(() => {
								this.passages.vertices[i].edges[j].connection = this.connect(el, childEl);
							});

						}
					}
				}
			}
		},
		created: function () {
			jsPlumb.ready(function () {
				// Set up defaults for nice arrows with mostly straight lines
				jsPlumb.importDefaults({
					Overlays: [
						["Arrow", {location: 1, width: 15, height: 15, id: 'arrow'}]
					],
					Endpoints: ['Blank', 'Blank'],
					Connector: ['Bezier', {curviness: 20}],
					MaxConnections: 5,
					Anchor: ["Perimeter", {shape: "Square"}],

				});
				// jsPlumb.bind('connection', function (info, ev) {
				// 	// Check if not from mouse event, means initial setup connections
				// 	let sourceIndex = parseInt(info.source.parentElement.attributes['data-i'].value);
				// 	let targetIndex = parseInt(info.target.attributes['data-i'].value);
				// 	let targetId = app.passages.vertices[targetIndex].data.id;
				// 	app.passages.vertices[sourceIndex].addChildNode(targetId, info.connection);
				// });
				// jsPlumb.bind('connectionDetached', function (info, ev) {
				// 	let sourceIndex = parseInt(info.source.parentElement.attributes['data-i'].value);
				// 	let targetIndex = parseInt(info.target.attributes['data-i'].value);
				// 	let targetId = app.passages.vertices[targetIndex].data.id;
				// 	app.passages.vertices[sourceIndex].removeChild(targetId);
				// });
			});
		},
		mounted: function () {
			this.drawArrows();
		}

	})
;

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


