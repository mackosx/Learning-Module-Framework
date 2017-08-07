class Node {
	constructor(data) {
		this.data = data;
		this.edges = [];
	}

	addChild(from, to, id) {
		const index = Digraph.indexOfObject(id, this.edges);
		let conn = '';
		// Make sure node is not already pointed to id
		if (index <= -1) {
			jsPlumb.ready(() => {
				conn = this.connect(from, to);
			});
			this.edges.push({
				id: id,
				connection: conn
			});
		}
	}

	addChildNode(id, conn) {
		const index = Digraph.indexOfObject(id, this.edges);
		this.edges.push({
			id: id,
			connection: conn
		});
	}

	/**
	 * Establish a connection between two nodes
	 * @param from Originating DIV element
	 * @param to DIV element that the arrow will point to
	 * @returns {*} The jsPlumb Connection object
	 */
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
	}

	// Remove the edge attached to node with id
	removeChild(id) {
		const index = Digraph.indexOfObject(id, this.edges);
		if (~index) {
			jsPlumb.ready(() => {
				jsPlumb.deleteEndpoint(this.edges[index].connection.endpoints[0]);
				this.edges.splice(index, 1);
			});
		}
	}

	// Remove all edges originating from a node
	removeAllChildren() {
		jsPlumb.ready(() => {
			for (let i = 0; i < this.edges.length; i++) {
				let conn = this.edges[i].connection;
			}
			this.edges.splice(0);

		});
	}

	numberOfEdges() {
		return this.edges.length;
	}

	toJSON() {
		// implement json method so we can exclude jsPlumb connection object when converting
		let copy = {},
			exclude = {edges: 1};
		for (let prop in this) {
			if (this.hasOwnProperty(prop)) {
				if (!exclude[prop]) {
					copy[prop] = this[prop];
				} else if (prop == 'edges') {
					let newEdges = [];
					for (let i = 0; i < this.edges.length; i++) {
						newEdges.push({id: this.edges[i].id});
					}
					copy[prop] = newEdges;
				}
			}
		}

		return copy;

	}
}


class Digraph {
	constructor() {
		this.vertices = [];
	}

	static indexOfObject(id, array) {
		let index = -1;
		for (let i = 0, len = array.length; i < len; i++) {
			if (array[i].id == id || array[i] == id) {
				index = i;
				break;
			}
		}
		return index;
	}

	addVertex(data) {
		this.vertices.push(new Node(data));

	}

	removeVertex(id) {
		const index = this.getIndexOf(id);
		this.removeEdgesTo(id);
		if (~index) {
			jsPlumb.ready(() => {
				jsPlumb.removeAllEndpoints(this.vertices[index].data.element);
			});
			this.vertices.splice(index, 1);
		}
	}

	getIndexOf(id) {
		for (let i = 0; i < this.vertices.length; i++) {
			if (this.vertices[i].data.id == id) {
				return i;
			}
		}
		return -1;
	}

	removeEdgesTo(id) {
		for (let i = 0; i < this.vertices.length; i++) {
			// Removes any edges that point to the vertex with id
			this.vertices[i].removeChild(id)
		}
	}

	size() {
		return this.vertices.length;
	}

	relations() {
		return this.numberOfEdges;
	}

	compactToJSON(title, desc) {
		const s = {
			data: this,
			title: title,
			desc: desc
		};
		let json = JSON.stringify(s)
		document.getElementById('rpg-stories').value = json;
		return json;
	}

	importFromJSON(story) {
		for (let i = 0; i < story.data.vertices.length; i++) {
			let oldNode = story.data.vertices[i];
			let newNode = new Node(oldNode.data);
			newNode.edges = oldNode.edges;
			this.vertices[i] = newNode;
		}
		console.log('Successfully imported previous story.')
		return {
			title: story.title,
			desc: story.desc
		}
	}


}