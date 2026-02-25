(function(){
  let modalWrap = null;
  function buildModal({title, bodyHtml, showInput, inputPlaceholder, inputValue, okText, cancelText}){
    if (modalWrap) modalWrap.remove();
    modalWrap = document.createElement('div');
    modalWrap.innerHTML = `
    <div class="modal fade" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">${title || "Notice"}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div>${bodyHtml || ""}</div>
            ${
              showInput
                ? `<input type="text" class="form-control mt-3" id="plantixDialogInput" placeholder="${htmlEscape(
                    inputPlaceholder || ""
                  )}" value="${htmlEscape(
                    inputValue || ""
                  )}" data-label="${htmlEscape(
                    inputPlaceholder || "Dialog input"
                  )}">`
                : ""
            }
          </div>
          <div class="modal-footer">
            ${
              cancelText
                ? `<button type="button" class="btn btn-border" data-bs-dismiss="modal">${cancelText}</button>`
                : ""
            }
            ${
              okText
                ? `<button type="button" class="btn btn-theme" id="plantixDialogOk">${okText}</button>`
                : ""
            }
          </div>
        </div>
      </div>
    </div>`;
    document.body.appendChild(modalWrap);
    return modalWrap.querySelector('.modal');
  }

  function htmlEscape(s){ return (s||'').replace(/[&<>"]/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;"}[c])); }

  function alertDialog({title='Notice', message='', okText='OK'}={}){
    return new Promise(resolve=>{
      const modalEl = buildModal({title, bodyHtml: htmlEscape(message), okText, cancelText: null});
      const modal = new bootstrap.Modal(modalEl);
      modalEl.querySelector('#plantixDialogOk').addEventListener('click', ()=>{ modal.hide(); });
      modalEl.addEventListener('hidden.bs.modal', ()=>{ resolve(); });
      modal.show();
    });
  }

  function confirmDialog({title='Please confirm', message='', okText='Yes', cancelText='No'}={}){
    return new Promise(resolve=>{
      const modalEl = buildModal({title, bodyHtml: htmlEscape(message), okText, cancelText});
      const modal = new bootstrap.Modal(modalEl);
      modalEl.querySelector('#plantixDialogOk').addEventListener('click', ()=>{ resolve(true); modal.hide(); });
      modalEl.addEventListener('hidden.bs.modal', ()=>{ resolve(false); });
      modal.show();
    });
  }

  function promptDialog({title='Input', message='', placeholder='', defaultValue='', okText='Submit', cancelText='Cancel'}={}){
    return new Promise(resolve=>{
      const modalEl = buildModal({title, bodyHtml: htmlEscape(message), showInput:true, inputPlaceholder:placeholder, inputValue: defaultValue, okText, cancelText});
      const modal = new bootstrap.Modal(modalEl);
      const input = modalEl.querySelector('#plantixDialogInput');
      modalEl.querySelector('#plantixDialogOk').addEventListener('click', ()=>{ resolve(input.value); modal.hide(); });
      modalEl.addEventListener('hidden.bs.modal', ()=>{ resolve(null); });
      modal.show();
      setTimeout(()=>{ input?.focus(); }, 200);
    });
  }

  window.Dialog = { alert: alertDialog, confirm: confirmDialog, prompt: promptDialog };
})();
