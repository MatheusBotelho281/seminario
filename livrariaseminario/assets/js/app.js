(function(){
  const root=document.documentElement, body=document.body;
  const savedTheme=localStorage.getItem('livraria-theme'); if(savedTheme==='dark') body.classList.add('dark');
  const fs=localStorage.getItem('livraria-font'); if(fs) root.style.fontSize=fs+'px';
})();
function toggleTheme(){document.body.classList.toggle('dark');localStorage.setItem('livraria-theme',document.body.classList.contains('dark')?'dark':'light')}
function fontSizeStep(step){const r=document.documentElement;let s=parseFloat(getComputedStyle(r).fontSize)||16;s=Math.max(13,Math.min(20,s+step));r.style.fontSize=s+'px';localStorage.setItem('livraria-font',s)}
function toggleSidebar(){document.getElementById('sidebar')?.classList.toggle('open')}
function confirmAction(message){return window.confirm(message||'Confirma esta operação?')}
function formatBRL(value){return new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'}).format(Number(value)||0)}
function printPage(){window.print()}
function showModal(id){document.getElementById(id)?.classList.add('open')}
function hideModal(id){document.getElementById(id)?.classList.remove('open')}
