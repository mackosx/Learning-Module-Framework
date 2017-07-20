Vue.component('passage', {
	//language=HTML
	template: `
        <div class="passages">

            <h3>{{current.name}}</h3>
            <p>{{current.desc}}</p>
            <ul class="options">
                <template v-for="option in passages.edges[current.id]">
	                <li @click="change(option)"><a>{{'Choose : ' + passages.objects[option].name}}</a></li>
                </template>
            </ul>
        </div>
	`,
	props: ['current', 'change', 'passages']
});

let e = new Vue({
	el: '#' + data[0],
	data: {
		passages: data[2],
		currentPassage: data[1]
	},
	methods: {
		changeCurrentPassage(indexToChangeTo){
			this.currentPassage = this.passages.objects[indexToChangeTo];
		}
	}
});