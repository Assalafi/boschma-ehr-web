document.addEventListener('DOMContentLoaded',function(){
var tabs=['triaged','inConsultation','awaitingLab','awaitingPharmacy','completedToday'],S={};
tabs.forEach(function(id){
var p=document.getElementById(id);if(!p)return;
var tb=p.querySelector('tbody');if(!tb)return;
var all=Array.from(tb.querySelectorAll('tr'));
var dr=all.filter(function(r){return !r.querySelector('td[colspan]')});
var er=all.find(function(r){return r.querySelector('td[colspan]')});
S[id]={all:dr,fil:dr.slice(),emp:er,pg:1,pp:15,q:''};
var si=p.querySelector('.tab-search-input'),db;
if(si)si.addEventListener('input',function(){clearTimeout(db);var v=this;db=setTimeout(function(){S[id].q=v.value.toLowerCase().trim();S[id].pg=1;go(id)},200)});
var ps=p.querySelector('.tab-per-page');
if(ps)ps.addEventListener('change',function(){S[id].pp=parseInt(this.value)||0;S[id].pg=1;go(id)});
go(id);
});
function go(id){
var s=S[id];if(!s)return;
s.fil=s.q?s.all.filter(function(r){return r.textContent.toLowerCase().indexOf(s.q)!==-1}):s.all.slice();
var t=s.fil.length,pp=s.pp||t||1,tp=Math.max(1,Math.ceil(t/pp));
if(s.pg>tp)s.pg=tp;
var st=(s.pg-1)*pp,en=s.pp?st+pp:t;
s.all.forEach(function(r){r.style.display='none'});
s.fil.forEach(function(r,i){r.style.display=(i>=st&&i<en)?'':'none'});
if(s.emp)s.emp.style.display=(t===0&&!s.q)?'':'none';
var nm=document.querySelector('.tab-no-match[data-tab="'+id+'"]');
if(nm)nm.style.display=(t===0&&s.q)?'block':'none';
var inf=document.querySelector('.tab-info[data-tab="'+id+'"]');
if(inf){if(s.q)inf.textContent=t+' of '+s.all.length+' records';
else if(s.pp&&t>s.pp)inf.textContent=(st+1)+'\u2013'+Math.min(en,t)+' of '+t;
else inf.textContent=t+' records';}
pgn(id,tp);
}
function pgn(id,tp){
var el=document.querySelector('.tab-pagination[data-tab="'+id+'"]');
if(!el)return;var s=S[id];
if(tp<=1||!s.pp){el.innerHTML='';el.style.display='none';return;}
el.style.display='flex';var h='';
h+='<button class="tab-page-btn" data-p="'+(s.pg-1)+'"'+(s.pg===1?' disabled':'')+'>&#8249;</button>';
var pgs=[];
if(tp<=7){for(var i=1;i<=tp;i++)pgs.push(i)}
else{pgs=[1];if(s.pg>3)pgs.push('...');
for(var i=Math.max(2,s.pg-1);i<=Math.min(tp-1,s.pg+1);i++)pgs.push(i);
if(s.pg<tp-2)pgs.push('...');pgs.push(tp);}
pgs.forEach(function(pg){
if(pg==='...')h+='<span style="padding:0 4px;color:#94a3b8">\u2026</span>';
else h+='<button class="tab-page-btn'+(pg===s.pg?' active':'')+'" data-p="'+pg+'">'+pg+'</button>';
});
h+='<button class="tab-page-btn" data-p="'+(s.pg+1)+'"'+(s.pg===tp?' disabled':'')+'>&#8250;</button>';
el.innerHTML=h;
el.querySelectorAll('.tab-page-btn:not([disabled])').forEach(function(b){
b.addEventListener('click',function(){var pg=parseInt(this.getAttribute('data-p'));
if(pg>=1&&pg<=tp){s.pg=pg;go(id);document.getElementById(id).scrollIntoView({behavior:'smooth',block:'start'})}});
});
}
});
