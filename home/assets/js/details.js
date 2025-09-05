// Navbar background toggle
(function(){ const navbar=document.getElementById('mainNavbar'); function update(){ if(window.scrollY>60) navbar.classList.add('scrolled'); else navbar.classList.remove('scrolled'); } update(); window.addEventListener('scroll', update); })();

// Smooth scroll for local anchors
document.querySelectorAll('a[href^="#"]').forEach(a=>{ a.addEventListener('click', function(e){ const href=this.getAttribute('href'); if(href.length>1 && document.querySelector(href)){ e.preventDefault(); const top=document.querySelector(href).getBoundingClientRect().top + window.scrollY - 72; window.scrollTo({ top, behavior:'smooth' }); } }); });

// GSAP animations
window.addEventListener('load', ()=>{
  if(typeof gsap!=='undefined'){
    if(typeof ScrollTrigger!=='undefined') gsap.registerPlugin(ScrollTrigger);
    gsap.from('.details-card',{ opacity:0, y:40, duration:0.9, ease:'power2.out' });
    gsap.from('.property-media .main-photo',{ scale:1.03, duration:1.6, ease:'power2.out' });
    gsap.utils.toArray('.property-card').forEach((card,i)=>{
      gsap.from(card,{ scrollTrigger:{ trigger:card, start:'top 90%' }, opacity:0, y:30, duration:0.7, delay:i*0.08, ease:'power2.out' });
    });
    document.querySelectorAll('.btn').forEach(btn=>{ btn.addEventListener('mouseenter', ()=> gsap.to(btn,{ scale:1.03, duration:0.14 }) ); btn.addEventListener('mouseleave', ()=> gsap.to(btn,{ scale:1, duration:0.14 }) ); });
  }
});
