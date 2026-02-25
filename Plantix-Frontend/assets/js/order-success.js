// Order success page: show last order summary
(function(){
  document.addEventListener('DOMContentLoaded',()=>{
    const all=JSON.parse(localStorage.getItem('plantixOrders')||'[]');
    const lastId=localStorage.getItem('plantixLastOrderId');
    const order=all.find(o=>o.id===lastId) || all[all.length-1];
    const container=document.querySelector('.order-success-area .container');
    if (!container || !order) return;
    const div=document.createElement('div');
    div.className='mt-4';
    div.innerHTML=`
      <div class="alert alert-success"><i class="fas fa-check-circle"></i> Your order <strong>${order.id}</strong> has been placed successfully.</div>
      <div class="table-responsive">
        <table class="table table-bordered"><tbody>
          <tr><th>Date</th><td>${new Date(order.createdAt).toLocaleString()}</td></tr>
          <tr><th>Total</th><td>PKR ${order.total.toLocaleString()}</td></tr>
          <tr><th>Status</th><td>${order.status}</td></tr>
          <tr><th>Payment</th><td>${order.paymentMethod}</td></tr>
        </tbody></table>
      </div>
      <button class="btn btn-success" id="viewOrdersBtn">View Order History</button>
    `;
    container.appendChild(div);
    document.getElementById('viewOrdersBtn')?.addEventListener('click',()=>{
      window.location.href = 'orders.html';
    });
  });
})();
