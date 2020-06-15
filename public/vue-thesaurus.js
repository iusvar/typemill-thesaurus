let thesaurus = new Vue({
	delimiters: ['${', '}'],
	el: '#menu',
	data: {
		node_raw_content: document.querySelector('#content')
	},
  
	mounted: function(){
		if(this.node_raw_content){
			this.node_raw_content.addEventListener('contextmenu', this.onContextMenu);
		}
		document.addEventListener('click', this.onClick);
	},
  
	methods: {

		onClick: function(e){
			//e.preventDefault();
			e.stopPropagation();
			let choice = event.target.getAttribute('word');
			if(choice !== null){

				let text = document.querySelector('#content').value;
				start = document.querySelector('#thesaurus_start').value;
				end = document.querySelector('#thesaurus_end').value;

				let before = text.substring(0, start);
				let after = text.substring(end);
				let modified = before + choice + after;

				editor.form.content = modified;

				document.querySelector('#draft').disabled = false;
				document.querySelector('#publish').disabled = false;

			}
			let menu = document.querySelector('#thesaurus_menu');
			menu.classList.remove('show-menu');
		},


		onContextMenu: function(e){
			e.preventDefault();
			e.stopPropagation();

			// content text
			let text = event.target.value;

			// position of the word within the text when the right mouse button is pressed
			let start = event.target.selectionStart;
			let end = event.target.selectionEnd;

			document.querySelector('#thesaurus_start').value = start;
			document.querySelector('#thesaurus_end').value = end;

			// gets the word to look for
			let lookup  = text.slice(start, end);

			// highlight the word
			let node_raw_content = document.querySelector('#content');
			if(node_raw_content){
				node_raw_content.setSelectionRange(start, end);
			}

			// NO LONGER NEEDED?
			if(lookup === ""){
				return false;
			}

			// makes a copy of the word to be searched and the position the menu will have
			document.querySelector('#thesaurus_search_for').value = lookup;
			document.querySelector('#thesaurus_pagex').value = e.pageX;
			document.querySelector('#thesaurus_pagey').value = e.pageY + 11;

			myaxios.get('/meanings_tool',{
				params: {
					'search_for': lookup
				}
			})
			.then(function (response) {
				let pagex = document.querySelector('#thesaurus_pagex').value;
				let pagey = document.querySelector('#thesaurus_pagey').value;
				let menu = document.querySelector('#thesaurus_menu');

				let html = response.data;
				if( html.includes('ERROR') ) {
					Toastify({text: html}).showToast();
					/*Toastify({
					text: html,
					duration: 3000,
					destination: 'https://github.com/apvarun/toastify-js',
					newWindow: true,
					close: false,
					gravity: "top", // `top` or `bottom`
					positionLeft: true, // `true` or `false`
					backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
					}).showToast();*/
					menu.style.display = 'none';
				} else {
					menu.innerHTML = html;
					menu.style.display = 'block';
					menu.style.left = pagex + 'px';
					menu.style.top = pagey + 'px';
					menu.classList.add('show-menu');
				}

			})
			.catch(function (error) {
				if(error.response) {
					console.log(error.response.data.errors);
				}
			});
		},

	}
});