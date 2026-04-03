/* =====================================================
   NaturasiMIS Dashboard Script
   RBAC handled by Laravel backend
===================================================== */

const escapeHtml = (text) => {
  const map = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" };
  return String(text).replace(/[&<>"']/g, m => map[m]);
};

const openModal = (modal, focusEl = null) => {
  modal?.classList.add("active");
  document.body.classList.add("modal-open");
  if (focusEl) setTimeout(() => focusEl?.focus(), 120);
};
const closeModal = (modal) => {
  modal?.classList.remove("active");
  document.body.classList.remove("modal-open");
};

window.showFloatingAlert = function(message, type = "success") {
  const alert = document.createElement("div");
  alert.className = "floating-alert";
  const icons = { error: "✖", warning: "⚠", info: "ℹ", success: "✔" };
  const colors = { error: "#c62828", warning: "#f57c00", info: "#0288d1" };
  alert.innerHTML = `<div style="display:flex;align-items:center;gap:10px;"><strong style="font-size:1.2rem;">${icons[type]}</strong><div>${message}</div></div>`;
  if (colors[type]) alert.style.background = colors[type];
  document.body.appendChild(alert);
  requestAnimationFrame(() => alert.classList.add("show"));
  setTimeout(() => { alert.classList.remove("show"); setTimeout(() => alert.remove(), 300); }, 3300);
};

document.addEventListener("DOMContentLoaded", () => {

  /* ===== PROFILE DROPDOWN ===== */
  const adminTrigger = document.querySelector(".admin-trigger");
  const dropdown = document.querySelector(".dropdown");

 adminTrigger?.addEventListener("click", (e) => {
    e.stopPropagation();
    const isOpen = dropdown?.classList.contains("show");
    document.querySelectorAll(".dropdown.show").forEach(d => d.classList.remove("show"));
    document.querySelectorAll(".admin-trigger.active").forEach(t => t.classList.remove("active"));
    if (!isOpen) {
      dropdown?.classList.add("show");
      adminTrigger.classList.add("active");
    }
  }); 

  document.addEventListener("click", (e) => {
    if (!adminTrigger?.contains(e.target) && !dropdown?.contains(e.target)) {
      dropdown?.classList.remove("show");
      adminTrigger?.classList.remove("active");
    }
  });

  /* ===== ACCOUNT SETTINGS ===== */
  const accountSettingsBtn = document.querySelector("#accountSettingsBtn");
  const accountSettingsModal = document.getElementById("accountSettingsModal");
  const closeAccountSettings = document.getElementById("closeAccountSettings");

  accountSettingsBtn?.addEventListener("click", () => {
    openModal(accountSettingsModal);
    dropdown?.classList.remove("show");
    adminTrigger?.classList.remove("active");
  });

  closeAccountSettings?.addEventListener("click", () => closeModal(accountSettingsModal));

  /* ===== LOGOUT MODAL ===== */
  const logoutBtn = document.querySelector("#logoutBtn");
  const logoutModal = document.querySelector("#logoutModal");
  const cancelLogout = document.querySelector("#cancelLogout");

  logoutBtn?.addEventListener("click", (e) => {
    e.stopPropagation();
    openModal(logoutModal);
    dropdown?.classList.remove("show");
  });

  cancelLogout?.addEventListener("click", () => closeModal(logoutModal));

  /* ===== LOW STOCK MODAL ===== */
  const lowStockCard = document.getElementById("lowStockCard");
  const lowStockModal = document.getElementById("lowStockModal");
  const closeLowStock = document.getElementById("closeLowStock");

  lowStockCard?.addEventListener("click", () => openModal(lowStockModal));
  closeLowStock?.addEventListener("click", () => closeModal(lowStockModal));

  /* ===== MANAGE USERS ===== */
  const openAddUserBtn = document.getElementById("openAddUser");
  const addUserModal = document.getElementById("addUserModal");
  const closeAddUser = document.getElementById("closeAddUser");
  const editUserModal = document.getElementById("editUserModal");
  const closeEditUser = document.getElementById("closeEditUser");
  const deleteUserModal = document.getElementById("deleteUserModal");
  const cancelDeleteUser = document.getElementById("cancelDeleteUser");

  openAddUserBtn?.addEventListener("click", () => openModal(addUserModal));
  closeAddUser?.addEventListener("click", () => closeModal(addUserModal));
  closeEditUser?.addEventListener("click", () => closeModal(editUserModal));
  cancelDeleteUser?.addEventListener("click", () => closeModal(deleteUserModal));

  /* ===== ADD NEW INVENTORY ITEM ===== */
  const openAddItemBtn = document.getElementById("openAddItem");
  const addItemModal = document.getElementById("addItemModal");
  const closeAddItemBtn = document.getElementById("closeAddItem");

  openAddItemBtn?.addEventListener("click", () => openModal(addItemModal));
  closeAddItemBtn?.addEventListener("click", () => closeModal(addItemModal));

  /* ===== ADD NEW PRODUCTION BATCH ===== */
  const openAddBatchBtn = document.getElementById("openAddBatch");
  const addBatchModal = document.getElementById("addBatchModal");
  const closeAddBatchBtn = document.getElementById("closeAddBatch");

  openAddBatchBtn?.addEventListener("click", () => openModal(addBatchModal));
  closeAddBatchBtn?.addEventListener("click", () => closeModal(addBatchModal));

  /* ===== BATCHES PAGE ===== */
  const openAddBatchFromBatches = document.getElementById("openAddBatchFromBatches");
  const addBatchModalBatches = document.getElementById("addBatchModalBatches");
  const closeAddBatchBatches = document.getElementById("closeAddBatchBatches");
  const viewBatchModal = document.getElementById("viewBatchModal");
  const closeViewBatch = document.getElementById("closeViewBatch");
  const editBatchModal2 = document.getElementById("editBatchModal");
  const deleteBatchModal2 = document.getElementById("deleteBatchModal");
  const closeEditBatch2 = document.getElementById("closeEditBatch");
  const cancelDeleteBatch2 = document.getElementById("cancelDeleteBatch");
  const confirmDeleteBatch2 = document.getElementById("confirmDeleteBatch");

  openAddBatchFromBatches?.addEventListener("click", () => openModal(addBatchModalBatches));
  closeAddBatchBatches?.addEventListener("click", () => closeModal(addBatchModalBatches));
  closeViewBatch?.addEventListener("click", () => closeModal(viewBatchModal));
  closeEditBatch2?.addEventListener("click", () => closeModal(editBatchModal2));
  cancelDeleteBatch2?.addEventListener("click", () => closeModal(deleteBatchModal2));

  document.addEventListener("click", (e) => {
    if (e.target.closest(".view-btn") && e.target.closest("table#batchesTable")) {
      const row = e.target.closest("tr");
      document.getElementById("viewBatchId").textContent    = row.children[0].textContent;
      document.getElementById("viewCheeseType").textContent = row.children[1].textContent;
      document.getElementById("viewQuantity").textContent   = row.children[2].textContent;
      document.getElementById("viewStart").textContent      = row.children[3].textContent;
      document.getElementById("viewEnd").textContent        = row.children[4].textContent;
      document.getElementById("viewStatus").textContent     = row.children[5].textContent;
      document.getElementById("viewStaff").textContent      = row.children[6].textContent;
      document.getElementById("viewRemarks").textContent    = row.children[7].textContent;
      openModal(viewBatchModal);
    }
  });

  /* ===== INVENTORY EDIT & DELETE MODALS ===== */
  const editItemModal = document.getElementById("editItemModal");
  const deleteItemModal = document.getElementById("deleteItemModal");
  const closeEditItem = document.getElementById("closeEditItem");
  const cancelDelete = document.getElementById("cancelDelete");
  const confirmDelete = document.getElementById("confirmDelete");

  closeEditItem?.addEventListener("click", () => closeModal(editItemModal));
  cancelDelete?.addEventListener("click", () => closeModal(deleteItemModal));

  let currentDeleteRow = null;
  document.addEventListener("click", (e) => {
    if (e.target.closest(".delete-btn") && e.target.closest("table#inventoryTable")) {
      currentDeleteRow = e.target.closest("tr");
      openModal(deleteItemModal);
    }
  });
  confirmDelete?.addEventListener("click", () => {
    const form = currentDeleteRow?.querySelector(".delete-form");
    if (form) form.submit();
    closeModal(deleteItemModal);
  });

  /* ===== PRODUCTION EDIT & DELETE MODALS ===== */
  const editBatchModal = document.getElementById("editBatchModal");
  const deleteBatchModal = document.getElementById("deleteBatchModal");
  const closeEditBatch = document.getElementById("closeEditBatch");
  const cancelDeleteBatch = document.getElementById("cancelDeleteBatch");
  const confirmDeleteBatch = document.getElementById("confirmDeleteBatch");

  closeEditBatch?.addEventListener("click", () => closeModal(editBatchModal));
  cancelDeleteBatch?.addEventListener("click", () => closeModal(deleteBatchModal));

  let currentDeleteBatch = null;
  document.addEventListener("click", (e) => {
    if (e.target.closest(".delete-btn") && e.target.closest("table#productionTable")) {
      currentDeleteBatch = e.target.closest("tr");
      openModal(deleteBatchModal);
    }
    if (e.target.closest(".delete-btn") && e.target.closest("table#batchesTable")) {
      openModal(deleteBatchModal2);
    }
  });
  confirmDeleteBatch?.addEventListener("click", () => {
    if (currentDeleteBatch) currentDeleteBatch.remove();
    closeModal(deleteBatchModal);
    showFloatingAlert("Batch deleted successfully", "success");
  });

  /* ===== REPORTS PAGE ===== */
  const refreshReportsBtn = document.getElementById("refreshReports");
  const printReportBtn = document.getElementById("printReport");
  const exportPDFBtn = document.getElementById("exportPDF");

  refreshReportsBtn?.addEventListener("click", () => {
    const icon = refreshReportsBtn.querySelector("i");
    icon?.classList.add("fa-spin");
    setTimeout(() => { icon?.classList.remove("fa-spin"); showFloatingAlert("Reports refreshed", "success"); }, 1000);
  });

  printReportBtn?.addEventListener("click", () => window.print());

  exportPDFBtn?.addEventListener("click", async () => {
    showFloatingAlert("Generating PDF... Please wait", "info");
    if (typeof jspdf === 'undefined') {
      const script = document.createElement('script');
      script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
      script.onload = () => generatePDF();
      document.head.appendChild(script);
    } else {
      generatePDF();
    }
  });

  function generatePDF() {
    try {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF('p', 'mm', 'a4');
      const reportType = document.getElementById('reportType')?.value || 'General';
      const currentDate = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
      doc.setFontSize(20); doc.setTextColor(14, 71, 45);
      doc.text('NaturasiMIS', 105, 20, { align: 'center' });
      doc.setFontSize(16);
      doc.text(`${reportType.charAt(0).toUpperCase() + reportType.slice(1)} Report`, 105, 30, { align: 'center' });
      doc.setFontSize(10); doc.setTextColor(100, 100, 100);
      doc.text(`Generated: ${currentDate}`, 105, 38, { align: 'center' });
      doc.setDrawColor(232, 245, 233); doc.setLineWidth(0.5); doc.line(20, 48, 190, 48);
      let yPosition = 58;
      const reportTable = document.getElementById('reportTable');
      const headers = Array.from(reportTable.querySelectorAll('thead th')).map(th => th.textContent);
      const rows = Array.from(reportTable.querySelectorAll('tbody tr')).map(tr =>
        Array.from(tr.querySelectorAll('td')).map(td => { const s = td.querySelector('.status-tag'); return s ? s.textContent : td.textContent; })
      );
      doc.setFontSize(10); doc.setFont('helvetica', 'bold'); doc.setTextColor(255, 255, 255);
      doc.setFillColor(14, 71, 45); doc.rect(20, yPosition - 5, 170, 8, 'F');
      const colWidth = 170 / headers.length;
      headers.forEach((h, i) => doc.text(h, 20 + (i * colWidth) + 2, yPosition, { maxWidth: colWidth - 4 }));
      yPosition += 10; doc.setFont('helvetica', 'normal'); doc.setTextColor(0, 0, 0);
      rows.forEach((row, ri) => {
        if (yPosition > 270) { doc.addPage(); yPosition = 20; }
        if (ri % 2 === 0) { doc.setFillColor(250, 250, 250); doc.rect(20, yPosition - 5, 170, 8, 'F'); }
        row.forEach((cell, i) => doc.text(String(cell), 20 + (i * colWidth) + 2, yPosition, { maxWidth: colWidth - 4 }));
        yPosition += 8;
      });
      const pageCount = doc.internal.getNumberOfPages();
      for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i); doc.setFontSize(8); doc.setTextColor(150, 150, 150);
        doc.text(`Page ${i} of ${pageCount}`, 105, 290, { align: 'center' });
        doc.text('NaturasiMIS © 2025 - Confidential', 20, 290);
      }
      doc.save(`NaturasiMIS_${reportType}_Report_${new Date().toISOString().split('T')[0]}.pdf`);
      showFloatingAlert("PDF exported successfully!", "success");
    } catch (error) {
      showFloatingAlert("Failed to generate PDF. Please try again.", "error");
    }
  }

  /* ===== SYSTEM SETTINGS ===== */
  const changePasswordBtn = document.getElementById("changePasswordBtn");
  const changePasswordModal = document.getElementById("changePasswordModal");
  const closePasswordModal = document.getElementById("closePasswordModal");
  const editSystemInfoBtn = document.getElementById("editSystemInfoBtn");
  const editSystemInfoModal = document.getElementById("editSystemInfoModal");
  const closeEditSystem = document.getElementById("closeEditSystem");

  changePasswordBtn?.addEventListener("click", () => openModal(changePasswordModal));
  closePasswordModal?.addEventListener("click", () => closeModal(changePasswordModal));
  editSystemInfoBtn?.addEventListener("click", () => openModal(editSystemInfoModal));
  closeEditSystem?.addEventListener("click", () => closeModal(editSystemInfoModal));

  document.getElementById("restoreBtn")?.addEventListener("click", () => {
    if (confirm("Are you sure you want to restore from backup? This will overwrite current data.")) {
      showFloatingAlert("Restore feature coming soon", "info");
    }
  });

  document.getElementById("resetSystemBtn")?.addEventListener("click", () => {
    if (confirm("⚠️ WARNING: This will reset all system data. Are you absolutely sure?")) {
      if (confirm("This action CANNOT be undone. Proceed with system reset?")) {
        showFloatingAlert("System reset feature coming soon", "info");
      }
    }
  });

  /* ===== REFRESH BUTTON SPIN EFFECT ===== */
  document.querySelectorAll(".btn-refresh").forEach((btn) => {
    btn.addEventListener("click", () => {
      const icon = btn.querySelector("i");
      icon?.classList.add("fa-spin");
      setTimeout(() => icon?.classList.remove("fa-spin"), 1000);
    });
  });

});
