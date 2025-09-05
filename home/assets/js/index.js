// Navbar background change on scroll
window.addEventListener('scroll', function() {
	const navbar = document.getElementById('mainNavbar');
	if (window.scrollY > 60) {
		navbar.classList.add('scrolled');
	} else {
		navbar.classList.remove('scrolled');
	}
});

// Smooth scrolling for nav links
document.querySelectorAll('.nav-link[href^="#"], .footer-link[href^="#"]').forEach(link => {
	link.addEventListener('click', function(e) {
		const target = document.querySelector(this.getAttribute('href'));
		if (target) {
			e.preventDefault();
			window.scrollTo({
				top: target.offsetTop - 70,
				behavior: 'smooth'
			});
		}
	});
});

// GSAP Animations
window.addEventListener('DOMContentLoaded', () => {
	// register ScrollTrigger plugin
	if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
		gsap.registerPlugin(ScrollTrigger);
	}
	// Hero text animation
	gsap.to('.hero-content h1', { opacity: 1, y: 0, duration: 1, delay: 0.2, ease: "power2.out" });
	gsap.to('.hero-content p', { opacity: 1, y: 0, duration: 1, delay: 0.6, ease: "power2.out" });
	gsap.to('.explore-btn', { opacity: 1, y: 0, duration: 1, delay: 1, ease: "power2.out" });

	// Section fade-in on scroll
	gsap.utils.toArray('section').forEach(section => {
		gsap.from(section, {
			scrollTrigger: {
				trigger: section,
				start: "top 80%",
				toggleActions: "play none none none"
			},
			opacity: 0,
			y: 60,
			duration: 0.8,
			ease: "power2.out"
		});
	});

	// Property card hover animation
	document.querySelectorAll('.property-card').forEach(card => {
		card.addEventListener('mouseenter', () => {
			gsap.to(card, { scale: 1.04, boxShadow: "0 12px 32px rgba(13,110,253,0.15)", duration: 0.2 });
		});
		card.addEventListener('mouseleave', () => {
			gsap.to(card, { scale: 1, boxShadow: "0 8px 32px rgba(13,110,253,0.10)", duration: 0.2 });
		});
	});

	// Search button animation
	gsap.from('.search-btn', {
		scrollTrigger: {
			trigger: '.search-btn',
			start: "top 90%",
			toggleActions: "play none none none"
		},
		opacity: 0,
		y: 40,
		duration: 0.7,
		ease: "power2.out"
	});
	// Swiper.js for property cards slider (initialize after DOM ready)
	if (typeof Swiper !== 'undefined') {
		const swiper = new Swiper('.property-swiper', {
			slidesPerView: 1,
			spaceBetween: 24,
			loop: true,
			pagination: {
				el: '.swiper-pagination',
				clickable: true
			},
			navigation: {
				nextEl: '.swiper-button-next',
				prevEl: '.swiper-button-prev'
			},
			breakpoints: {
				576: { slidesPerView: 1 },
				768: { slidesPerView: 2 },
				992: { slidesPerView: 3 }
			}
		});
	}
});

