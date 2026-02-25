// Shared logic for standalone auth/account pages
(function(){
  function qs(sel){ return document.querySelector(sel); }
  function qsa(sel){ return Array.from(document.querySelectorAll(sel)); }
  function setVal(id, v){ const el=document.getElementById(id); if(el) el.value=v; }
  function val(id){ const el=document.getElementById(id); return el?el.value:''; }
  function redirectTo(url){ window.location.href=url; }

  // Manage redirect memory
  function rememberRedirect(){ try{ sessionStorage.setItem('plantixRedirect', location.pathname+location.search); }catch(e){} }
  function consumeRedirect(defaultUrl){ try{ const r=sessionStorage.getItem('plantixRedirect'); if(r){ sessionStorage.removeItem('plantixRedirect'); return r; } }catch(e){} return defaultUrl; }

  function guardAuthOrRedirect(target){
    if (!window.Auth) return; // auth.js must load
    if (!Auth.current()){
      rememberRedirect();
      redirectTo('signin.html');
      return false;
    }
    return true;
  }

  // Sign In page
  function wireSignIn(){
    const form = qs('#signin-form'); if(!form) return;
    // If already authenticated, redirect to the appropriate dashboard immediately
    try{
      if (window.ExpertAuth && ExpertAuth.current()){
        const to = consumeRedirect('expert-dashboard.html');
        return redirectTo(to);
      }
      if (window.Auth && Auth.current()){
        const to = consumeRedirect('account-profile.html');
        return redirectTo(to);
      }
    }catch(e){}
    form.addEventListener('submit', function(e){
      e.preventDefault();
      const email=val('signinEmail'); const password=val('signinPassword');
      try {
        const role=(document.querySelector('input[name="signinRole"]:checked')?.value)||'customer';
        if (role==='expert' && window.ExpertAuth){
          ExpertAuth.signIn({email,password});
          const to = consumeRedirect('expert-dashboard.html');
          redirectTo(to);
        } else {
          Auth.signIn({email,password});
          // Always send customers to their dashboard after login (ignore remembered redirect)
          redirectTo('account-profile.html');
        }
      } catch(err){ if (window.Dialog) Dialog.alert({title:'Sign In', message: err.message||'Sign in failed'}); else alert(err.message||'Sign in failed'); }
    });
    // Link to sign up maintains redirect memory
    const link=qs('#go-to-signup');
    if (link) link.addEventListener('click', function(){ rememberRedirect(); });
  }

  // Sign Up page
  function wireSignUp(){
    const form = qs('#signup-form'); if(!form) return;
    form.addEventListener('submit', function(e){
      e.preventDefault();
      const name=val('signupName'); const email=val('signupEmail'); const password=val('signupPassword'); const phone=val('signupPhone');
      try {
        Auth.signUp({name,email,password,phone});
        const to = consumeRedirect('account-profile.html');
        redirectTo(to);
  } catch(err){ if (window.Dialog) Dialog.alert({title:'Sign Up', message: err.message||'Sign up failed'}); else alert(err.message||'Sign up failed'); }
    });
    const link=qs('#go-to-signin');
    if (link) link.addEventListener('click', function(){ rememberRedirect(); });
  }

  // Forgot Password page
  function wireForgot(){
    const form = qs('#forgot-form'); if(!form) return;
    form.addEventListener('submit', function(e){ e.preventDefault();
      const email = val('forgotEmail');
      try{
        // Pick role by selector if present or by URL param
        const roleSel = document.querySelector('input[name="forgotRole"]:checked')?.value;
        const role = roleSel || (new URL(location.href).searchParams.get('role')||'').toLowerCase();
        const useExpert = (role==='expert') && window.ExpertAuth;
        const info = useExpert ? ExpertAuth.requestPasswordReset(email) : Auth.requestPasswordReset(email);
        if (window.Toast) Toast.show('Reset code sent (demo)','success');
        const hint = qs('#demoTokenHint');
        if (hint){ hint.classList.remove('hidden'); hint.textContent = `Demo: use code ${info.token} within ${info.expiresInMinutes} minutes.`; }
        const extra = useExpert ? '&role=expert' : '';
        setTimeout(()=> redirectTo('password-reset.html?email='+encodeURIComponent(info.email)+extra), 800);
      }catch(err){ if (window.Dialog) Dialog.alert({title:'Forgot password', message: err.message||'Unable to send reset code'}); else alert(err.message||'Unable to send reset code'); }
    });
  }

  // Reset Password page
  function wireReset(){
    const form = qs('#reset-form'); if(!form) return;
    const emailField = qs('#resetEmail');
    const tokenField = qs('#resetToken');
    try{ const u=new URL(location.href); const e=u.searchParams.get('email'); if(e && emailField) emailField.value=e; }catch(e){}
    form.addEventListener('submit', function(e){ e.preventDefault();
      const email=emailField?.value||''; const token=tokenField?.value||''; const newPassword=val('resetNewPassword');
      try{
        const roleSel = document.querySelector('input[name="resetRole"]:checked')?.value;
        const role = roleSel || (new URL(location.href).searchParams.get('role')||'').toLowerCase();
        const useExpert = (role==='expert') && window.ExpertAuth;
        if (useExpert) { ExpertAuth.resetPassword({email, token, newPassword}); }
        else { Auth.resetPassword({email, token, newPassword}); }
        if (window.Toast) Toast.show('Password reset successful','success');
        const extra = useExpert ? '?role=expert' : '';
        setTimeout(()=> redirectTo('signin.html'+extra), 800);
      }catch(err){ if (window.Dialog) Dialog.alert({title:'Reset password', message: err.message||'Unable to reset password'}); else alert(err.message||'Unable to reset password'); }
    });
  }

  // Account Profile page
  function wireProfile(){
    const container = qs('#account-profile-page'); if(!container) return;
    if (!guardAuthOrRedirect()) return;
    const u = Auth.current();
    setVal('profName', u.name||'');
    setVal('profEmail', u.email||'');
    setVal('profPhone', u.phone||'');
    const a=u.address||{};
    setVal('profFirstName', a.firstName||'');
    setVal('profLastName', a.lastName||'');
    setVal('profCompany', a.company||'');
    setVal('profCountry', a.country||'Pakistan');
    setVal('profCity', a.city||'');
    setVal('profState', a.state||'');
    setVal('profPostal', a.postal||'');
    setVal('profAddress1', a.line1||'');
    setVal('profAddress2', a.line2||'');

    qs('#profile-form')?.addEventListener('submit', function(e){
      e.preventDefault();
      const patch={
        name: val('profName'),
        phone: val('profPhone'),
        address:{
          firstName: val('profFirstName'), lastName: val('profLastName'), company: val('profCompany'), country: val('profCountry'),
          city: val('profCity'), state: val('profState'), postal: val('profPostal'), line1: val('profAddress1'), line2: val('profAddress2'),
          phone: val('profPhone'), email: u.email
        }
      };
      Auth.updateProfile(patch);
      if (window.Toast) Toast.show('Profile saved','success'); else if (window.Dialog) Dialog.alert({title:'Profile', message:'Profile saved'}); else alert('Profile saved');
    });

    qs('#viewOrdersLink')?.addEventListener('click', function(e){ e.preventDefault(); redirectTo('orders.html'); });
  }

  function wireOrders(){
    const container = qs('#orders-page'); if(!container) return;
    if (!guardAuthOrRedirect()) return;
    const orders = Auth.getOrders();
    // Preferred: populate existing tbody if present
    const tbody = qs('#ordersTableBody');
    if (tbody){
      if (orders.length===0){ tbody.innerHTML = '<tr><td colspan="6">No orders yet.</td></tr>'; return; }
      tbody.innerHTML = orders.map(o=>{
        const withinMinute = (Date.now() - o.createdAt) <= 60000;
        const canCancel = withinMinute && o.status==='Processing';
        const canReturn = !withinMinute && o.status!=='Cancelled' && !o.returnedAt;
        const actions = `
          ${canCancel ? `<button class="btn btn-sm btn-outline-danger" data-action="cancel" data-id="${o.id}">Cancel</button>` : ''}
          ${canReturn ? `<button class="btn btn-sm btn-outline-secondary ms-1" data-action="return" data-id="${o.id}">Request Return</button>` : ''}
        `;
        return `
        <tr>
          <td><a href="order-details.html?id=${encodeURIComponent(o.id)}">${o.id}</a></td>
          <td>${new Date(o.createdAt).toLocaleString()}</td>
          <td>${(o.items?o.items.length:0)}</td>
          <td>${o.total?.toLocaleString?.() || o.total}</td>
          <td>${o.status}</td>
          <td>${actions}</td>
        </tr>
      `;}).join('');
      // Action handlers
      tbody.querySelectorAll('button[data-action="cancel"]').forEach(btn=>{
        btn.addEventListener('click', function(){
          const id=this.getAttribute('data-id');
          const proceed = window.Dialog ? Dialog.confirm({title:'Cancel order', message:'Cancel this order? This is only possible within 1 minute of placing the order.'}) : Promise.resolve(confirm('Cancel this order? This is only possible within 1 minute of placing the order.'));
          proceed.then(ok=>{ if(!ok) return; try{ Auth.cancelOrder(id); (window.Toast?Toast.show('Order cancelled','success') : alert('Order cancelled')); wireOrders(); }catch(e){ (window.Toast?Toast.show(e.message||'Unable to cancel','error') : alert(e.message||'Unable to cancel')); } });
        });
      });
      tbody.querySelectorAll('button[data-action="return"]').forEach(btn=>{
        btn.addEventListener('click', function(){
          const id=this.getAttribute('data-id');
          const ask = window.Dialog ? Dialog.prompt({title:'Request return', message:'Reason for return (optional):', placeholder:'Enter reason (optional)', defaultValue:''}) : Promise.resolve(prompt('Reason for return (optional):',''));
          ask.then(reason=>{ if (reason===null) return; try{ Auth.requestReturn(id, reason||''); (window.Toast?Toast.show('Return requested','info') : alert('Return requested')); wireOrders(); }catch(e){ (window.Toast?Toast.show(e.message||'Unable to request return','error') : alert(e.message||'Unable to request return')); } });
        });
      });
      return;
    }
    // Fallback: build full table into #ordersListTable
    const list = qs('#ordersListTable');
    if (!list) return;
    if (orders.length===0){ list.innerHTML='<p>No orders yet.</p>'; return; }
    list.innerHTML = `
      <div class="table-responsive">
        <table class="table table-bordered table-striped">
          <thead><tr><th>Order #</th><th>Date</th><th>Total (PKR)</th><th>Status</th><th>Payment</th></tr></thead>
          <tbody>
            ${orders.map(o=>`<tr>
              <td><a href="order-details.html?id=${encodeURIComponent(o.id)}">${o.id}</a></td>
              <td>${new Date(o.createdAt).toLocaleString()}</td>
              <td>${o.total?.toLocaleString?.() || o.total}</td>
              <td>${o.status}</td>
              <td>${o.paymentMethod||'-'}</td>
            </tr>`).join('')}
          </tbody>
        </table>
      </div>`;
  }

  document.addEventListener('DOMContentLoaded', function(){
    wireSignIn();
    wireSignUp();
    wireForgot();
    wireReset();
    wireProfile();
    wireOrders();
    rewireHeaderForStandalone();
  });
  
  // On standalone pages, make the header button navigate to pages (not modals)
  function rewireHeaderForStandalone(){
    const isStandalone = !!(qs('#account-profile-page') || qs('#orders-page') || qs('#signin-form') || qs('#signup-form'));
    if (!isStandalone) return;
    const link = document.querySelector('.attr-nav .button a');
    if (!link) return;
    const container = link.closest('li.button')?.parentElement || document.querySelector('.attr-nav ul');
    const clone = link.cloneNode(true);
    function setLink(text, href){ clone.textContent = text; clone.setAttribute('href', href); }
    if (window.ExpertAuth && ExpertAuth.current()) {
      setLink('Expert Panel', 'expert-dashboard.html');
      // Add Sign out button next to it
      const signoutLi = document.createElement('li');
      const signoutA = document.createElement('a');
      signoutA.textContent = 'Sign out';
      signoutA.href = '#';
      signoutA.addEventListener('click', function(e){ e.preventDefault(); try{ ExpertAuth.signOut(); window.location.href='signin.html?role=expert'; }catch(err){} });
      signoutLi.className = 'button';
      signoutLi.appendChild(signoutA);
      if (container) container.appendChild(signoutLi);
    } else if (window.Auth && Auth.current()) {
      setLink('Account', 'account-profile.html');
      // Add Sign out button next to it
      const signoutLi = document.createElement('li');
      const signoutA = document.createElement('a');
      signoutA.textContent = 'Sign out';
      signoutA.href = '#';
      signoutA.addEventListener('click', function(e){ e.preventDefault(); try{ Auth.signOut(); window.location.href='signin.html'; }catch(err){} });
      signoutLi.className = 'button';
      signoutLi.appendChild(signoutA);
      if (container) container.appendChild(signoutLi);
    } else if (qs('#signin-form')) {
      setLink('Register', 'signup.html');
    } else if (qs('#signup-form')) {
      setLink('Sign In', 'signin.html');
    } else {
      setLink('Register', 'signup.html');
    }
    link.parentNode.replaceChild(clone, link);
  }
})();
