Vue.component('passage', {
	//language=HTML
	template: `
        <div class="passages">
            <h3>{{current.data.name}}</h3>
            <p>{{current.data.desc}}</p>
            <ul class="options">
                <template v-for="child in current.edges">
	                <li @click="change(passages.getIndexOf(child.id))"><a>{{'Choose : ' + passages.vertices[passages.getIndexOf(child.id)].data.name}}</a></li>
                </template>
            </ul>
	        <div class="end" v-if="current.data.isEnd">
		        <p>You have completed this section.</p>
		        <p>You scored {{score}} points.</p>
		        <button type="button" @click="again"><p>Play Again</p></button>
	        </div>
     
        </div>
	`,
	props: ['current', 'change', 'passages', 'score', 'again']
});

let e = new Vue({
	el: '#' + rpgData[0],
	data: {
		passages: importData(rpgData[2], rpgData[1]),
		currentPassage: rpgData[1],
		score: 0
	},
	methods: {
		changeCurrentPassage(indexToChangeTo){
			this.currentPassage = this.passages.vertices[indexToChangeTo];
			this.addPoints();
		},
		addPoints(){
			this.score += parseInt(this.currentPassage.data.weight);
		},
		playAgain(){
			this.score = 0;
			this.currentPassage = rpgData[1];
		}
	}
});
//TODO: Add transition effect to story display
function importData(data, x){
	let d = new Digraph();
	d.vertices = data['vertices'];
	return d;
}