/*
* Variables
* */

const header = document.querySelector('#header')
const logo = document.querySelector('#logo')
const navToggle = document.querySelector('#mobile-nav-trigger')
const main = document.querySelector('#main')
const footer = document.querySelector('#footer')

/*
* Click-Listener for Responsive Navigation
* */

navToggle.addEventListener('click', e => {
	if (navToggle.classList.contains('slideRight')) {
		header.classList.toggle('menu-show')
		logo.classList.toggle('menu-show')
		main.classList.toggle('menu-show')
		footer.classList.toggle('menu-show')
	} else {
    	header.classList.toggle('menu-show')
    }
})

/*
* scroll handler for header transforms
* */

window.addEventListener('scroll', e => {
    header.classList.add('scroll')

    if (window.scrollY <= 100) {
        header.classList.remove('scroll')
    }
})

/*
* change title on switching to another browser tab
* */

const origTitle = document.title

window.onblur = function(){
    document.title = "Nicht vergessen!"
}

window.onfocus = function(){
    document.title = origTitle
}