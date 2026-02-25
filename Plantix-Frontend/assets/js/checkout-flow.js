// Checkout page logic: populate summary and place order
(function(){
  document.addEventListener('DOMContentLoaded',()=>{
    if (typeof CartManager==='undefined') return;
    handlePaymentReturn();
    renderSummary();
    prefillFromProfile();
    document.getElementById('place-order-btn')?.addEventListener('click', placeOrder);
    wirePromo();
  });

  function getPromo(){ try{ return JSON.parse(localStorage.getItem('plantixPromo')||'null'); }catch(e){ return null; } }
  function setPromo(p){ if(!p) localStorage.removeItem('plantixPromo'); else localStorage.setItem('plantixPromo', JSON.stringify(p)); }

  // Simple promo rules
  // PLANTIX10 -> 10% off subtotal
  // FREESHIP  -> shipping 0
  // SAVE500   -> PKR 500 off subtotal if subtotal >= 3000
  function evaluatePromo(code, {subtotal}){
    // normalize code by stripping non-alphanumeric characters then uppercasing
    const c=(code||'').toString().replace(/[^A-Z0-9]/gi,'').toUpperCase();
    if (!c) return { ok:false, message:'Enter a promo code' };
    if (c==='PLANTIX10') return { ok:true, type:'percent', value:10, message:'10% off applied' };
    if (c==='FREESHIP') return { ok:true, type:'freeship', value:0, message:'Free shipping applied' };
    if (c==='SAVE500') {
      if (subtotal>=3000) return { ok:true, type:'flat', value:500, message:'PKR 500 off applied' };
      return { ok:false, message:'Minimum order PKR 3000 for SAVE500' };
    }
    return { ok:false, message:'Invalid promo code' };
  }

  function renderSummary(){
    const cart=CartManager.getCart();
    const list=document.getElementById('order-items-list');
    if (!list) return;
    if (cart.length===0) { list.innerHTML='<p>Your cart is empty.</p>'; }
    else {
      list.innerHTML=cart.map(item=>`
        <div class="order-item d-flex justify-content-between border-bottom py-2">
          <span>${item.name} x ${item.quantity}</span>
          <span>PKR ${(item.price*item.quantity).toLocaleString()}</span>
        </div>
      `).join('');
    }
    const subtotal=CartManager.getCartTotal();
    // promo
    let shipping=500; let discount=0; let applied=null;
    const saved=getPromo();
    if (saved && saved.code){
      const ev=evaluatePromo(saved.code,{subtotal});
      if (ev.ok){ applied=Object.assign({code:saved.code}, ev); if (ev.type==='percent') discount=Math.round(subtotal*ev.value/100); if (ev.type==='flat') discount=Math.min(subtotal, ev.value); if (ev.type==='freeship') shipping=0; }
      else { setPromo(null); }
    }
    const taxable=Math.max(0, subtotal-discount);
    const tax=Math.round(taxable*0.05);
    const total=taxable+shipping+tax;
    document.getElementById('checkout-subtotal').textContent='PKR '+subtotal.toLocaleString();
    const discountRow=document.getElementById('discount-row');
    const discountVal=document.getElementById('checkout-discount');
    if (discount>0){ discountRow?.classList.remove('hidden'); discountVal.textContent='- PKR '+discount.toLocaleString(); }
    else { discountRow?.classList.add('hidden'); }
    document.getElementById('checkout-shipping').textContent='PKR '+shipping.toLocaleString();
    document.getElementById('checkout-tax').textContent='PKR '+tax.toLocaleString();
    document.getElementById('checkout-total').textContent='PKR '+total.toLocaleString();
  }

  function placeOrder(){
    if (!window.Auth || !Auth.current()) { if (window.AuthUI) AuthUI.openAuthModal(); return; }
    const user=Auth.current();
    const cart=CartManager.getCart();
  if (cart.length===0) { if (window.Dialog) { Dialog.alert({title:'Cart', message:'Your cart is empty'});} else { alert('Your cart is empty'); } return; }

    // Run strict validation on the form
    const form = document.getElementById('checkout-form');
    // Ensure StrictValidation is loaded
    if (typeof window.StrictValidation === 'undefined') {
        const msg = "System Error: strict-validation.js not loaded.";
        if (window.Dialog && window.Dialog.alert) Dialog.alert({title:'Error', message:msg});
        else alert(msg);
        return;
    }
    
    // Validate
    if (!StrictValidation.validateForm(form)) {
        return;
    }

    // Terms acceptance
    const terms=document.getElementById('termsCheckbox');
    if (terms && !terms.checked){ if (window.Dialog){ Dialog.alert({title:'Terms & Conditions', message:'Please accept the terms and conditions to proceed.'}); } else { alert('Please accept the terms and conditions to proceed.'); } return; }

    // Collect billing
    const formValues=collectForm();
    const subtotal=CartManager.getCartTotal();
    // apply promo one more time to be safe
    let shipping=500; let discount=0; let promo=null; const saved=getPromo();
    if (saved && saved.code){
      const ev=evaluatePromo(saved.code,{subtotal});
      if (ev.ok){ promo=Object.assign({code:saved.code}, ev); if (ev.type==='percent') discount=Math.round(subtotal*ev.value/100); if (ev.type==='flat') discount=Math.min(subtotal, ev.value); if (ev.type==='freeship') shipping=0; }
    }
    const taxable=Math.max(0, subtotal-discount);
    const tax=Math.round(taxable*0.05);
    const total=taxable+shipping+tax;
    // Simulated Stripe branch for online payment
    if ((formValues.paymentMethod||'')==='online'){
      const pending={ userId:user.id, items:cart, subtotal, discount, shipping, tax, total, promo, address:formValues.address, paymentMethod:'online', status:'Processing', createdAt:Date.now() };
      try{ localStorage.setItem('plantixPendingOrder', JSON.stringify(pending)); }catch(e){}
      // Defer order creation to payment page
      window.location.href='stripe-sim.html';
      return;
    }

    const order={ id:'o_'+Date.now(), userId:user.id, items:cart, subtotal, discount, shipping, tax, total, promo, address:formValues.address, paymentMethod:formValues.paymentMethod, status:'Processing', createdAt:Date.now() };
    Auth.recordOrder(order);
    // Save address back to profile for next time
    try {
      Auth.updateProfile({ address: {
        firstName: formValues.address.firstName,
        lastName: formValues.address.lastName,
        city: formValues.address.city,
        state: formValues.address.state,
        postal: formValues.address.postal,
        line1: formValues.address.line1,
        line2: formValues.address.line2,
        phone: formValues.address.phone,
        email: formValues.address.email
      }});
    } catch(e) {}
    CartManager.clearCart();
    // clear promo after successful order
    setPromo(null);
    window.location.href='order-success.html';
  }

  function collectForm(){
    const addr={
      firstName: val('firstName'), lastName: val('lastName'), company: val('companyName'), country: val('country'),
      line1: val('address1'), line2: val('address2'), city: val('city'), state: val('state'), postal: val('postalCode'), phone: val('phone'), email: val('email'), notes: val('orderNotes')
    };
    const pm=document.querySelector('input[name=paymentMethod]:checked')?.value||'cod';
    return { address:addr, paymentMethod:pm };
  }
  function val(id){ const el=document.getElementById(id); return el?el.value:''; }

  function prefillFromProfile(){
    if (!window.Auth) return;
    const u=Auth.current();
    if (!u) return;
    const a=u.address||{};
    setVal('firstName', a.firstName || (u.name?u.name.split(' ')[0]:'') );
    setVal('lastName', a.lastName || (u.name?u.name.split(' ').slice(1).join(' '):'') );
    setVal('companyName', a.company||'');
    setVal('country', a.country||'Pakistan');
    setVal('address1', a.line1||'');
    setVal('address2', a.line2||'');
    setVal('city', a.city||'');
    setVal('state', a.state||'');
    setVal('postalCode', a.postal||'');
    setVal('phone', u.phone||a.phone||'');
    setVal('email', u.email||a.email||'');
  }
  function setVal(id, v){ const el=document.getElementById(id); if (el) el.value=v; }

  function wirePromo(){
    const applyBtn=document.getElementById('applyPromoBtn');
    const removeBtn=document.getElementById('removePromoBtn');
    const input=document.getElementById('promoCodeInput');
    const help=document.getElementById('promoHelp');
    if (!applyBtn || !input) return;
    // preload saved
    const saved=getPromo();
    if (saved && saved.code){
      input.value=saved.code; 
      removeBtn?.classList.remove('hidden');
      const res=evaluatePromo(saved.code, { subtotal: CartManager.getCartTotal() });
      if (res.ok){ help.textContent=res.message; help.classList.add('text-success'); help.classList.remove('text-danger','text-muted'); }
    }
    applyBtn.addEventListener('click', ()=>{
      const code=(input.value||'').trim();
      const res=evaluatePromo(code, { subtotal: CartManager.getCartTotal() });
      if (!res.ok){ help.textContent=res.message; help.classList.remove('text-success'); help.classList.remove('text-muted'); help.classList.add('text-danger'); setPromo(null); removeBtn?.classList.add('hidden'); if (window.Toast) Toast.show(res.message,'error'); }
      else { setPromo({code}); help.textContent=res.message; help.classList.add('text-success'); help.classList.remove('text-danger'); help.classList.remove('text-muted'); removeBtn?.classList.remove('hidden'); if (window.Toast) Toast.show(res.message,'success'); }
      renderSummary();
    });
    removeBtn?.addEventListener('click', ()=>{ setPromo(null); input.value=''; help.textContent=''; help.classList.remove('text-success','text-danger'); help.classList.add('text-muted'); removeBtn.classList.add('hidden'); if (window.Toast) Toast.show('Promo removed','info'); renderSummary(); });
  }

  function handlePaymentReturn(){
    try{
      const res = JSON.parse(localStorage.getItem('plantixPaymentResult')||'null');
      if (res){
        if (res.status==='canceled' && window.Toast){ Toast.show('Payment canceled','error'); }
        if (res.status==='failed' && window.Toast){ Toast.show('Payment failed','error'); }
        localStorage.removeItem('plantixPaymentResult');
      }
    }catch(e){}
  }
})();
