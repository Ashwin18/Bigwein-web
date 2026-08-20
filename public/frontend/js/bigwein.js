'use strict';
/* BIGWEIN FRONTEND JS — bigweinadmin.codegensolutions.com */

async function bwFetch(url, method, body) {
  method = method || 'GET';
  var h = {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':BW.csrf};
  var o = {method:method, headers:h};
  if (body && method !== 'GET') o.body = JSON.stringify(body);
  try { var r = await fetch(url, o); return await r.json(); }
  catch(e) { return {error:true, message:'Network error. Please try again.'}; }
}

async function bwPost(url, data) { return bwFetch(url, 'POST', data); }

function bwToast(msg, type) {
  type = type || 'success';
  var t = document.getElementById('bwToast');
  if (!t) return;
  t.textContent = msg;
  t.className = 'bw-toast ' + type + ' show';
  setTimeout(function(){ t.classList.remove('show'); }, 3200);
}

document.addEventListener('DOMContentLoaded', function() {
  var tog = document.getElementById('bwToggle');
  var men = document.getElementById('mobileMenu');
  if (tog && men) {
    tog.addEventListener('click', function(){ men.classList.toggle('open'); });
    document.addEventListener('click', function(e){
      if (!tog.contains(e.target) && !men.contains(e.target)) men.classList.remove('open');
    });
  }
  var hdr = document.getElementById('bwHeader');
  if (hdr) {
    window.addEventListener('scroll', function(){
      hdr.style.boxShadow = window.scrollY > 8 ? '0 2px 16px rgba(0,0,0,.1)' : '0 1px 6px rgba(0,0,0,.06)';
    }, {passive:true});
  }
});

function bwGoFavs(){ window.location.href = BW.isLoggedIn ? '/user/saved' : '/user/login'; }

async function bwLogout(){ await bwFetch('/bw-api/logout','POST'); window.location.href='/'; }

async function bwFav(btn, propId){
  if (!BW.isLoggedIn) {
    bwToast('Please login to save properties','error');
    setTimeout(function(){ window.location.href='/user/login'; }, 900);
    return;
  }
  btn.disabled = true;
  var res = await bwPost('/bw-api/favourite', {property_id: propId});
  btn.disabled = false;
  if (!res.error) {
    var liked = res.action === 'added';
    btn.classList.toggle('liked', liked);
    var icon = btn.querySelector('i');
    if (icon) icon.className = 'fa-' + (liked?'solid':'regular') + ' fa-heart';
    bwToast(res.message, 'success');
  } else {
    bwToast(res.message, 'error');
  }
}

function bwFaq(btn){
  var item   = btn.closest('.faq-item');
  var isOpen = item.classList.contains('open');
  document.querySelectorAll('.faq-item').forEach(function(f){ f.classList.remove('open'); });
  if (!isOpen) item.classList.add('open');
}
