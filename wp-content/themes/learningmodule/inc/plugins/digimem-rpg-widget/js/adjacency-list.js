class Node {
	constructor(data) {
		this.data = data;
		this.edges = [];
	}

	addChild(id) {
		const index = this.edges.indexOf(id);
		// Make sure node is not already pointed to id
		if (index <= -1) {
			this.edges.push(id);
		}
	}

	removeChild(id) {
		const index = this.edges.indexOf(id);
		if (~index) {
			this.edges.splice(index, 1);
		}
	}
	numberOfEdges(){
		return this.edges.length;
	}
}


class Digraph {
	constructor() {
		this.vertices = [];
	}


	addVertex(data) {
		this.vertices.push(new Node(data));
	}

	removeVertex(id) {
		this.removeEdgesTo(id);
		const index = this.getIndexOf(id);
		if (~index) {
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
			this.vertices[i].removeChild(id)
		}
	}

	size() {
		return this.vertices.length;
	}

	relations() {
		return this.numberOfEdges;
	}

	// traverseDFS(vertex, fn) {
	// 	if (!~this.vertices.indexOf(vertex)) {
	// 		return console.log('Vertex not found');
	// 	}
	// 	const visited = [];
	// 	this._traverseDFS(vertex, visited, fn);
	// }
	//
	// _traverseDFS(vertex, visited, fn) {
	// 	visited[vertex] = true;
	// 	if (this.edges[vertex] !== undefined) {
	// 		fn(vertex);
	// 	}
	// 	for (let i = 0; i < this.edges[vertex].length; i++) {
	// 		if (!visited[this.edges[vertex][i]]) {
	// 			this._traverseDFS(this.edges[vertex][i], visited, fn);
	// 		}
	// 	}
	// }
	//
	// traverseBFS(vertex, fn) {
	// 	if (!~this.vertices.indexOf(vertex)) {
	// 		return console.log('Vertex not found');
	// 	}
	// 	const queue = [];
	// 	queue.push(vertex);
	// 	const visited = [];
	// 	visited[vertex] = true;
	//
	// 	while (queue.length) {
	// 		vertex = queue.shift();
	// 		fn(vertex);
	// 		for (let i = 0; i < this.edges[vertex].length; i++) {
	// 			if (!visited[this.edges[vertex][i]]) {
	// 				visited[this.edges[vertex][i]] = true;
	// 				queue.push(this.edges[vertex][i]);
	// 			}
	// 		}
	// 	}
	// }
	//
	// pathFromTo(vertexSource, vertexDestination) {
	// 	if (!~this.vertices.indexOf(vertexSource)) {
	// 		return console.log('Vertex not found');
	// 	}
	// 	const queue = [];
	// 	queue.push(vertexSource);
	// 	const visited = [];
	// 	visited[vertexSource] = true;
	// 	const paths = [];
	//
	// 	while (queue.length) {
	// 		const vertex = queue.shift();
	// 		for (let i = 0; i < this.edges[vertex].length; i++) {
	// 			if (!visited[this.edges[vertex][i]]) {
	// 				visited[this.edges[vertex][i]] = true;
	// 				queue.push(this.edges[vertex][i]);
	// 				// save paths between vertices
	// 				paths[this.edges[vertex][i]] = vertex;
	// 			}
	// 		}
	// 	}
	// 	if (!visited[vertexDestination]) {
	// 		return undefined;
	// 	}
	//
	// 	const path = [];
	// 	for (let j = vertexDestination; j != vertexSource; j = paths[j]) {
	// 		path.push(j);
	// 	}
	// 	path.push(j);
	// 	return path.reverse().join('-');
	// }
	//
	// print() {
	// 	console.log(this.vertices.map(function (vertex) {
	// 		return (`${vertex} -> ${this.edges[vertex].join(', ')}`).trim();
	// 	}, this).join(' | '));
	// }

	compactToJSON(title, desc) {
		console.log(this);
		const s = {
			data: this,
			title: title,
			desc: desc
		};
		document.getElementById('rpg-stories').value = JSON.stringify(s);
		return JSON.stringify(s);
	}

	importFromJSON(story) {
		for(let i = 0; i < story.data.vertices.length; i++){
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