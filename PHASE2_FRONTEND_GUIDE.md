# Phase 2 - Frontend Implementation Guide

## ✅ What's Been Completed (Backend)

All backend infrastructure is ready:
- API endpoints for all new entities
- Controllers with CRUD operations
- File upload handling
- Email notifications
- Cron job for invoice generation

## 📋 Frontend Tasks Remaining

### 1. Create Vue Components

**Partner Companies Component** (`resources/js/components/PartnerCompanies/PartnerCompaniesIndex.vue`)
- CRUD interface for partner companies
- Fields: name, contact_person, contact_email, contact_phone, is_active
- Soft delete support

**Own Companies Component** (`resources/js/components/OwnCompanies/OwnCompaniesIndex.vue`)
- CRUD interface for own companies
- Fields: name, legal_name, tax_id, address, phone, email, is_active
- Soft delete support

**Contracts Component** (`resources/js/components/Contracts/ContractsIndex.vue`)
- CRUD interface with file upload
- Fields: partner_company, own_company, product, contract_number, dates, payment_type, amount, status
- File upload for contract documents
- View payment history (transactions) for each contract

**Transactions Component** (Update existing `resources/js/components/Transactions/TransactionsIndex.vue`)
- New structure: invoices linked to contracts
- Multiple file uploads
- Payment status management
- Send notification button
- Notes field

### 2. Armenian Translations

Add to table headers and labels:
- Անուն (Name)
- Կապ (Contact)
- Էլ․ Փոստ (Email)
- Հեռախոս (Phone)
- Կարգավիճակ (Status)
- Գործողություններ (Actions)
- Պայմանագիր (Contract)
- Ապրանք (Product)
- Ընկերություն (Company)
- Գումար (Amount)
- Վճարում (Payment)
- Ամսաթիվ (Date)

### 3. Update Vue Router

Add routes in `resources/js/app.js`:
```javascript
{ path: '/partner-companies', component: PartnerCompaniesIndex, meta: { requiresAuth: true }, name: 'partner-companies' },
{ path: '/own-companies', component: OwnCompaniesIndex, meta: { requiresAuth: true }, name: 'own-companies' },
{ path: '/contracts', component: ContractsIndex, meta: { requiresAuth: true }, name: 'contracts' },
{ path: '/contracts/:contractId/transactions', component: TransactionsIndex, meta: { requiresAuth: true }, name: 'contract-transactions' },
```

### 4. Update Navigation Header

Add menu items in `resources/js/components/Layout/Header.vue`:
- Partner Companies (Գործընկեր ընկերություններ)
- Own Companies (Մեր ընկերություններ)
- Contracts (Պայմանագրեր)
- Transactions (Գործարքներ)

## 🔧 Component Structure Pattern

Each component should follow this pattern (based on existing ProjectsIndex.vue):

```vue
<template>
  <div>
    <app-header></app-header>
    <div class="container mt-4">
      <!-- Header with Create button -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Title (Հայերեն)</h1>
        <button class="btn btn-success" @click="showCreateModal = true">
          New Item
        </button>
      </div>

      <!-- Table -->
      <table class="table table-striped table-bordered">
        <thead class="table-dark">
          <tr>
            <th>Անուն</th>
            <!-- Other headers -->
            <th>Գործողություններ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <!-- Table cells -->
            <td>
              <button class="btn btn-sm btn-primary" @click="editItem(item)">Edit</button>
              <button class="btn btn-sm btn-danger" @click="deleteItem(item.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Create/Edit Modal -->
      <div class="modal" :class="{ 'show d-block': showCreateModal }">
        <!-- Modal content -->
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      items: [],
      showCreateModal: false,
      isEditing: false,
      currentItem: {}
    };
  },
  mounted() {
    this.fetchItems();
  },
  methods: {
    async fetchItems() {
      const response = await axios.get('/api/endpoint');
      this.items = response.data.data;
    },
    async saveItem() {
      if (this.isEditing) {
        await axios.put(`/api/endpoint/${this.currentItem.id}`, this.currentItem);
      } else {
        await axios.post('/api/endpoint', this.currentItem);
      }
      this.fetchItems();
      this.closeModal();
    },
    async deleteItem(id) {
      if (confirm('Are you sure?')) {
        await axios.delete(`/api/endpoint/${id}`);
        this.fetchItems();
      }
    }
  }
};
</script>
```

## 📊 API Endpoints Reference

- Partner Companies: `/api/partner-companies`
- Own Companies: `/api/own-companies`
- Contracts: `/api/contracts`
- Transactions: `/api/transactions`
- File Upload: `/api/transactions/{id}/upload-files`
- Send Notification: `/api/transactions/{id}/send-notification`

## 🎯 Priority Order

1. **Partner Companies** - Simplest, start here
2. **Own Companies** - Similar to partner companies
3. **Contracts** - More complex (file upload, relationships)
4. **Transactions** - Most complex (multiple files, payment history)
5. **Armenian Translations** - Apply to all components
6. **Navigation** - Update header menu

## 📝 Notes

- All components use Bootstrap 5 styling
- Authentication handled via `$auth` helper
- File uploads use FormData with axios
- Soft deletes show/hide with is_active filter

---

**Would you like me to create one complete component as a working example, or provide the full code for all components?**
