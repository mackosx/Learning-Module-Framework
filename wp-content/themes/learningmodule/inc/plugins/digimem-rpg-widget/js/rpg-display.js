

Vue.component('passage', {
	//language=HTML
	template: `
        <div class="passages">
            <h3>{{current.data.name}}</h3>
            <p>{{current.data.desc}}</p>
            <ul class="options">
                <template v-for="id in current.edges">
	                <li @click="change(passages.getIndexOf(id))"><a>{{'Choose : ' + passages.vertices[passages.getIndexOf(id)].data.name}}</a></li>
                </template>
            </ul>
	        <div class="end" v-if="current.data.isEnd">
		        <p>You have completed this section.</p>
		        <p>You scored {{score}} points.</p>
	        </div>
     
        </div>
	`,
	props: ['current', 'change', 'passages', 'score']
});

let e = new Vue({
	el: '#' + data[0],
	data: {
		passages: importData(data[2], data[1]),
		currentPassage: data[1],
		score: 0
	},
	methods: {
		changeCurrentPassage(indexToChangeTo){
			this.currentPassage = this.passages.vertices[indexToChangeTo];
			this.addPoints();
		},
		addPoints(){
			this.score += parseInt(this.currentPassage.data.weight);
		}
	}
});
//TODO: Add transition effect to story display
function importData(data, x){
	let d = new Digraph();
	d.vertices = data['vertices'];
	return d;
}