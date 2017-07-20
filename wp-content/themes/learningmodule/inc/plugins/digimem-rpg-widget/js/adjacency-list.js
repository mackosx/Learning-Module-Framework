class Digraph{
	constructor(){
		this.objects = [];
		this.vertices = [];
		this.edges = [];
		this.numberOfEdges = 0;
	}
	addObject(obj){
		this.objects.push(obj);
		this.addVertex(obj.id);
	}
	removeObject(obj){
		const index = obj.id;
		if(~index) {
			this.objects.splice(index, 1);
		}
		this.removeVertex(index);
	}
	addVertex(vertex){
		this.vertices.push(vertex);
		this.edges[vertex] = [];
	}
	removeVertex(vertex){
		const index = this.vertices.indexOf(vertex);
		if(~index) {
			this.vertices.splice(index, 1);
		}
		while(this.edges[vertex].length){
			const adjacentVertex = this.edges[vertex].pop();
			this.removeEdge(adjacentVertex, vertex);
		}
	}
	addEdge(vertex1, vertex2){
		if(this.edges[vertex1].indexOf(vertex2) <= -1){
			this.edges[vertex1].push(vertex2);
			this.numberOfEdges++;
		}

	}
	removeEdge(vertex1, vertex2) {
		const index1 = this.edges[vertex1] ? this.edges[vertex1].indexOf(vertex2) : -1;
		if (~index1) {
			this.edges[vertex1].splice(index1, 1);
			this.numberOfEdges--;
		}

	}
	size() {
		return this.vertices.length;
	}

	relations() {
		return this.numberOfEdges;
	}
	traverseDFS(vertex, fn) {
		if(!~this.vertices.indexOf(vertex)) {
			return console.log('Vertex not found');
		}
		const visited = [];
		this._traverseDFS(vertex, visited, fn);
	}

	_traverseDFS(vertex, visited, fn) {
		visited[vertex] = true;
		if(this.edges[vertex] !== undefined) {
			fn(vertex);
		}
		for(let i = 0; i < this.edges[vertex].length; i++) {
			if(!visited[this.edges[vertex][i]]) {
				this._traverseDFS(this.edges[vertex][i], visited, fn);
			}
		}
	}

	traverseBFS(vertex, fn) {
		if(!~this.vertices.indexOf(vertex)) {
			return console.log('Vertex not found');
		}
		const queue = [];
		queue.push(vertex);
		const visited = [];
		visited[vertex] = true;

		while(queue.length) {
			vertex = queue.shift();
			fn(vertex);
			for(let i = 0; i < this.edges[vertex].length; i++) {
				if(!visited[this.edges[vertex][i]]) {
					visited[this.edges[vertex][i]] = true;
					queue.push(this.edges[vertex][i]);
				}
			}
		}
	}

	pathFromTo(vertexSource, vertexDestination) {
		if(!~this.vertices.indexOf(vertexSource)) {
			return console.log('Vertex not found');
		}
		const queue = [];
		queue.push(vertexSource);
		const visited = [];
		visited[vertexSource] = true;
		const paths = [];

		while(queue.length) {
			const vertex = queue.shift();
			for(let i = 0; i < this.edges[vertex].length; i++) {
				if(!visited[this.edges[vertex][i]]) {
					visited[this.edges[vertex][i]] = true;
					queue.push(this.edges[vertex][i]);
					// save paths between vertices
					paths[this.edges[vertex][i]] = vertex;
				}
			}
		}
		if(!visited[vertexDestination]) {
			return undefined;
		}

		const path = [];
		for(let j = vertexDestination; j != vertexSource; j = paths[j]) {
			path.push(j);
		}
		path.push(j);
		return path.reverse().join('-');
	}

	print() {
		console.log(this.vertices.map(function(vertex) {
			return (`${vertex} -> ${this.edges[vertex].join(', ')}`).trim();
		}, this).join(' | '));
	}
	compactToJSON(title, desc) {
		const s = {
			objects: this.objects,
			vertices: this.vertices,
			edges: this.edges,
			title: title,
			desc: desc
		};
		document.getElementById('rpg-stories').value = JSON.stringify(s);
		return JSON.stringify(s);
	}
	importFromJSON(story){
		this.objects = story.objects;
		this.edges = story.edges;
		this.vertices = story.vertices;
		console.log('Successfully imported previous story.')
		return {
			title: story.title,
			desc: story.desc
		}
	}


}