(function(){
  function ensureContainer(){
    let c=document.getElementById('plantixToastContainer');
    if (!c){
      c=document.createElement('div');
      c.id='plantixToastContainer';
      c.className='toast-container position-fixed top-0 end-0 p-3';
      c.setAttribute('style','z-index: 1080');
      document.body.appendChild(c);
    }
    return c;
  }
  function variantClasses(type){
    switch(type){
      case 'success': return 'bg-success text-white';
      case 'error': return 'bg-danger text-white';
      case 'warning': return 'bg-warning text-dark';
      case 'info':
      default: return 'bg-primary text-white';
    }
  }
  function showToast(message, type='info', options={}){
    const container=ensureContainer();
    const toast=document.createElement('div');
    toast.className=`toast align-items-center ${variantClasses(type)}`;
    toast.setAttribute('role','alert');
    toast.setAttribute('aria-live','assertive');
    toast.setAttribute('aria-atomic','true');
    toast.innerHTML=`<div class="d-flex">
      <div class="toast-body">${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>`;
    container.appendChild(toast);
    const t=new bootstrap.Toast(toast,{ delay: options.delay||3000, autohide: options.autohide!==false });
    t.show();
    toast.addEventListener('hidden.bs.toast', ()=>{ toast.remove(); });
    return t;
  }
  window.Toast={ show: showToast };
})();
