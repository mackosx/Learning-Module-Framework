Vue.component('passage', {
	//language=HTML
	template: `
		<transition name="fade" :duration="1000">
            <div class="passage" v-show="!transition">
                <h3>{{current.data.name}}</h3>
                <p>{{current.data.desc}}</p>
                <ul class="options">
                    <template v-for="child in current.edges">
                        <li @click="changePassage(passages.getIndexOf(child.id))"><a>{{'Choose : ' + passages.vertices[passages.getIndexOf(child.id)].data.name}}</a></li>
                    </template>
                </ul>
                <div class="end" v-if="current.data.isEnd">
	                {{sendScoreToDatabase()}}
                    <p>You have completed this section.</p>
                    <p>You scored {{score}} points.</p>
                    <button type="button" @click="again"><p>Play Again</p></button>
                </div>

            </div>
		</transition>
        
	`,
	props: ['current', 'change', 'passages', 'score', 'again', 'title'],
	data: function(){
		return {
			transition: false
		}
	},
	methods: {
		changePassage(index){
			this.transition = true;
			this.change(index);
			this.transition = false;
		},
		sendScoreToDatabase(){
			jQuery.ajax({
				url: rpg.ajaxUrl,
				type: 'POST',
				data: {
					action: 'submit_score',
					type: 'rpg',
					score: e.score
				},
				success: function(data){
					console.log('Stored.', e.score);
				}
			});
			return ' ';
		}
	}
});

let e = new Vue({
	el: '#' + rpgData[0],
	data: {
		passages: importData(rpgData[2]),
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
function importData(data){
	let d = new Digraph();
	d.vertices = data['vertices'];
	return d;
}
jQuery(document).ready(function(){
	jQuery('#' + rpgData[3]).on('click', function(){
		jQuery(this).hide(500);
		jQuery('#' + rpgData[4]).show(500);
	})
});
