      @extends('layouts.app')
      @section('title', 'Orders')
      @section('page-title', 'Production Orders')
      @section('page-subtitle', 'Manage client orders and automatically deduct ingredients from inventory.')

      @section('content')
      @section('suppressGlobalErrors', true)
      <section id="ordersSection">

        @if(session('success'))
        <div class="success-message" id="successAlert">
          <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif
        @if($errors->has('insufficient'))
        <div class="alert-message" id="errorAlert">
          <i class="fas fa-exclamation-circle"></i> {{ $errors->first('insufficient') }}
        </div>
        @endif

        <div class="module-header">
          <h2><i class="fas fa-clipboard-list"></i> Production Orders</h2>
          <div class="actions">
            <form method="GET" action="{{ route('orders.index') }}" style="display:flex;">
              <input type="hidden" name="tab" value="{{ $activeTab }}">
              <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search orders or clients..." class="input-search" value="{{ $search ?? '' }}" />
              </div>
            </form>
          @if(auth()->user()->role !== 'manager')
  <button class="btn-primary" id="openCreateOrder" style="display:{{ $activeTab === 'orders' ? 'inline-flex' : 'none' }};">
    <i class="fas fa-plus"></i> New Order
  </button>
  <button class="btn-primary" id="openAddClient" style="display:{{ $activeTab === 'clients' ? 'inline-flex' : 'none' }};">
    <i class="fas fa-plus"></i> Add Client
  </button>
  @endif
        </div>
        </div>

      {{-- TABS --}}
        <div style="display:flex; gap:1rem; margin-top:1rem; align-items:center; flex-wrap:wrap;">
        <button class="btn-tab {{ $activeTab === 'orders' ? 'active' : '' }}" onclick="switchMainTab('orders')" style="padding:8px 18px; font-size:0.85rem; border-radius:8px;">
            <i class="fas fa-clipboard-list"></i> Orders
          </button>
          <button class="btn-tab {{ $activeTab === 'archived-orders' ? 'active' : '' }}" onclick="switchMainTab('archived-orders')" style="padding:8px 18px; font-size:0.85rem; border-radius:8px;">
            <i class="fas fa-archive"></i> Archived Orders
          </button>
        <button class="btn-tab {{ $activeTab === 'clients' ? 'active' : '' }}" onclick="switchMainTab('clients')" style="padding:8px 18px; font-size:0.85rem; border-radius:8px;">
            <i class="fas fa-users"></i> Clients
          </button>
          <button class="btn-tab {{ $activeTab === 'archived-clients' ? 'active' : '' }}" onclick="switchMainTab('archived-clients')" style="padding:8px 18px; font-size:0.85rem; border-radius:8px;">
            <i class="fas fa-archive"></i> Archived Clients
          </button>
        </div>
        

      {{-- ORDERS TAB --}}
        <div id="mainTabOrders" style="display:{{ $activeTab === 'orders' ? 'block' : 'none' }};">
        <div class="table-container" style="margin-top:1rem;">
          <table>
            <thead>
              <tr>
                <th>P.O. Number</th>
                <th>Client</th>
                <th>Order Date</th>
                <th>Items</th>
                <th>Total (kg)</th>
                <th>Status</th>
                <th>Created By</th>
                <th>Confirmed At</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($orders as $order)
              <tr>
                <td>{{ $order->po_number ?? '—' }}</td>
                <td>{{ $order->client_name ?? '—' }}</td>
                <td>{{ $order->order_date ? $order->order_date->format('Y-m-d') : '—' }}</td>
                <td>
                  @foreach($order->items as $item)
                    <small style="display:block;">{{ $item->cheese_product }} {{ $item->variant_name }} × {{ number_format($item->quantity_pieces, 0) }}pcs</small>
                  @endforeach
                </td>
                <td>{{ number_format($order->quantity, 3) }} kg</td>
                <td>
                  <span class="status-tag {{ $order->status === 'Confirmed' ? 'active' : ($order->status === 'Cancelled' ? 'inactive' : ($order->status === 'Completed' ? 'completed' : 'low')) }}">
    {{ $order->status }}
  </span>
                </td>
                <td>{{ $order->createdBy?->username ?? 'N/A' }}</td>
                <td>{{ $order->confirmed_at ? $order->confirmed_at->format('Y-m-d H:i') : '—' }}</td>
                <td>
                  @if($order->status === 'Pending')
                    <button class="action-btn edit-btn edit-order-btn"
                      data-id="{{ $order->id }}"
                      data-client-id="{{ $order->client_id }}"
                      data-client-name="{{ $order->client_name }}"
                      data-order-date="{{ $order->order_date?->format('Y-m-d') }}"
                      data-notes="{{ $order->notes }}"
                      data-items="{{ json_encode($order->items) }}"
                      title="Edit">
                      <i class="fas fa-pen"></i>
                    </button>
                    <form method="POST" action="{{ route('orders.updateStatus', $order) }}" style="display:inline;">
                      @csrf @method('PATCH')
                      <input type="hidden" name="status" value="Confirmed">
                      <button type="button" class="btn-primary confirm-order-btn" style="padding:6px 12px; font-size:0.8rem;"
                        data-id="{{ $order->id }}"
                        data-items="{{ json_encode($order->items) }}">
                        <i class="fas fa-check"></i> Confirm
                      </button>
                    </form>
                    <form method="POST" action="{{ route('orders.updateStatus', $order) }}" style="display:inline; margin-left:4px;">
                      @csrf @method('PATCH')
                      <input type="hidden" name="status" value="Cancelled">
                      <button type="submit" class="btn-cancel" style="padding:6px 12px; font-size:0.8rem;">
                        <i class="fas fa-times"></i> Cancel
                      </button>
                    </form>
            @if(!in_array($order->status, ['Completed', 'Cancelled']))
    <button class="action-btn" title="Cannot archive (order not finished)" style="color:#ccc; cursor:not-allowed;" disabled>
      <i class="fas fa-archive"></i>
    </button>

  @elseif(in_array($order->status, ['Completed', 'Cancelled']))
    <button type="button" class="action-btn archive-order-btn" title="Archive" style="color:#e67e22;"
      data-id="{{ $order->id }}"
      data-url="{{ route('orders.archive', $order) }}">
      <i class="fas fa-archive"></i>
    </button>
  @endif
                  
                  @else
                    <span style="color:#aaa; font-size:0.85rem;">—</span>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="9" style="text-align:center;">No orders yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="pagination-wrapper">
          <p class="pagination-info">Showing {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} orders</p>
          <div class="custom-pagination-nav">
            @if($orders->onFirstPage()) <span class="pg-btn pg-disabled">&#8249;</span>
            @else <a href="{{ $orders->previousPageUrl() }}" class="pg-btn">&#8249;</a> @endif
            @php $current=$orders->currentPage(); $last=$orders->lastPage(); $pages=[]; for($i=1;$i<=$last;$i++){if($i==1||$i==$last||abs($i-$current)<=1)$pages[]=$i;} $pages=array_unique($pages); sort($pages); @endphp
            @php $prev=null; @endphp
            @foreach($pages as $page)
              @if($prev!==null && $page-$prev>1) <span class="pg-btn pg-dots">···</span> @endif
              @if($page==$current) <span class="pg-btn pg-active">{{ $page }}</span>
              @else <a href="{{ $orders->url($page) }}" class="pg-btn">{{ $page }}</a> @endif
              @php $prev=$page; @endphp
            @endforeach
            @if($orders->hasMorePages()) <a href="{{ $orders->nextPageUrl() }}" class="pg-btn">&#8250;</a>
            @else <span class="pg-btn pg-disabled">&#8250;</span> @endif
          </div>
        </div>
      </div>

      {{-- ARCHIVED ORDERS TAB --}}
        <div id="mainTabArchivedOrders" style="display:{{ $activeTab === 'archived-orders' ? 'block' : 'none' }};">
          <div class="table-container" style="margin-top:1rem;">
            <table>
              <thead>
                <tr>
                  <th>P.O. Number</th>
                  <th>Client</th>
                  <th>Order Date</th>
                  <th>Items</th>
                  <th>Total (kg)</th>
                  <th>Status</th>
                  <th>Created By</th>
                </tr>
              </thead>
              <tbody>
                @forelse($archivedOrders as $order)
                <tr>
                  <td>{{ $order->po_number ?? '—' }}</td>
                  <td>{{ $order->client_name ?? '—' }}</td>
                  <td>{{ $order->order_date ? $order->order_date->format('Y-m-d') : '—' }}</td>
                  <td>
                    @foreach($order->items as $item)
                      <small style="display:block;">{{ $item->cheese_product }} {{ $item->variant_name }} × {{ number_format($item->quantity_pieces, 0) }}pcs</small>
                    @endforeach
                  </td>
                  <td>{{ number_format($order->quantity, 3) }} kg</td>
                  <td><span class="status-tag inactive">{{ $order->status }}</span></td>
                  <td>{{ $order->createdBy?->username ?? 'N/A' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;">No archived orders.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

  {{-- CLIENTS TAB --}}
        <div id="mainTabClients" style="display:{{ $activeTab === 'clients' ? 'block' : 'none' }};">
        <div id="activeClientsTable">
          <div class="table-container" style="margin-top:1rem;">
            <table>
              <thead>
                <tr>
                  <th>Client Name</th>
                  <th>Contact Person</th>
                  <th>Phone</th>
                  <th>Email</th>
                  <th>Address</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($clients as $client)
                <tr>
                  <td>{{ $client->name }}</td>
                  <td>{{ $client->contact_person ?? '—' }}</td>
                  <td>{{ $client->phone ?? '—' }}</td>
                  <td>{{ $client->email ?? '—' }}</td>
                  <td>{{ $client->address ?? '—' }}</td>
                  <td>
                    <button class="action-btn edit-btn edit-client-btn"
                      data-id="{{ $client->id }}"
                      data-name="{{ $client->name }}"
                      data-contact="{{ $client->contact_person }}"
                      data-phone="{{ $client->phone }}"
                      data-email="{{ $client->email }}"
                      data-address="{{ $client->address }}">
                      <i class="fas fa-pen"></i>
                    </button>
                    @if($client->orders()->whereNotIn('status', ['Completed','Cancelled'])->exists())
    <button class="action-btn" title="Cannot archive (has active orders)" style="color:#ccc; cursor:not-allowed;" disabled>
      <i class="fas fa-archive"></i>
    </button>
  @else
    <form method="POST" action="{{ route('clients.archive', $client) }}" style="display:inline;">
      @csrf
      <button type="submit" class="action-btn" title="Archive" style="color:#e67e22;">
        <i class="fas fa-archive"></i>
      </button>
    </form>
  @endif
                  </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;">No clients yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
      </div>
        </div>

        <div id="mainTabArchivedClients" style="display:{{ $activeTab === 'archived-clients' ? 'block' : 'none' }};">
          <div class="table-container" style="margin-top:1rem;">
            <table>
              <thead>
                <tr>
                  <th>Client Name</th>
                  <th>Contact Person</th>
                  <th>Phone</th>
                  <th>Email</th>
                  <th>Address</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($archivedClients as $client)
                <tr>
                  <td>{{ $client->name }}</td>
                  <td>{{ $client->contact_person ?? '—' }}</td>
                  <td>{{ $client->phone ?? '—' }}</td>
                  <td>{{ $client->email ?? '—' }}</td>
                  <td>{{ $client->address ?? '—' }}</td>
                  <td>
                    <form method="POST" action="{{ route('clients.restore', $client) }}" style="display:inline;">
                      @csrf
                      <button type="submit" class="action-btn" title="Restore" style="color:#1a6b47;">
                        <i class="fas fa-undo"></i>
                      </button>
                    </form>
                    <form method="POST" action="{{ route('clients.destroy', $client) }}" method="POST" style="display:inline;">
                      @csrf @method('DELETE')
                      <button type="submit" class="action-btn delete-btn" title="Delete">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;">No archived clients.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
      </div>

      </section>
      @endsection

      @push('modals')

    {{-- CREATE ORDER MODAL --}}
      <div id="createOrderModal" class="modal">
        <div class="modal-content" style="max-width:780px; padding:0; overflow:hidden;">
          <div style="background:linear-gradient(135deg,#0e472d,#1a6b47); padding:1.5rem 2rem; display:flex; align-items:center; gap:12px;">
            <i class="fas fa-clipboard-list" style="color:#fff; font-size:1.4rem;"></i>
            <h2 style="color:#fff; margin:0; border:none; padding:0; font-size:1.4rem;">New Production Order</h2>
          </div>
          <div style="padding:2rem; overflow-y:auto; max-height:80vh;">

          <div id="step1">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
              <div class="form-group" style="margin:0; grid-column:span 2;">
                <label>Client <span style="color:red;">*</span></label>
                <select id="clientSelect" style="width:100%; padding:.6rem; border-radius:8px; border:1.5px solid #ccc;">
                  <option value="">Select Client</option>
                  @foreach($clients as $client)
                  <option value="{{ $client->id }}" data-name="{{ $client->name }}">{{ $client->name }}</option>
                  @endforeach
                  <option value="walk-in">Walk-in / Others</option>
                </select>
                <input type="text" id="clientNameInput" placeholder="Type client name..." style="width:100%; padding:.6rem; border-radius:8px; border:1.5px solid #ccc; margin-top:0.5rem; display:none;" />
              </div>
              <div class="form-group" style="margin:0;">
                <label>Order Date <span style="color:red;">*</span></label>
                <input type="date" id="orderDateInput" value="{{ now()->format('Y-m-d') }}" style="width:100%; padding:.6rem; border-radius:8px; border:1.5px solid #ccc;" />
              </div>
              <div class="form-group" style="margin:0;">
                <label>Notes</label>
                <input type="text" id="orderNotesInput" placeholder="Optional notes" style="width:100%; padding:.6rem; border-radius:8px; border:1.5px solid #ccc;" />
              </div>
            </div>

      <hr style="margin:1rem 0;">
            <p style="color:#666; margin-bottom:1rem; font-size:0.95rem;">Fill in the quantities for this order:</p>

            {{-- BURRATA GRID --}}
            <div style="margin-bottom:1.5rem;">
              <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem;">
                <span style="font-weight:700; color:#1a6b47; font-size:0.95rem;"><i class="fas fa-cheese"></i> Burrata</span>
                <span id="burrataTotal" style="font-size:0.8rem; color:#888;"></span>
              </div>
              <div style="display:flex; flex-wrap:wrap; gap:0.75rem;">
                @foreach($variants['Burrata'] ?? [] as $v)
                <div style="display:flex; flex-direction:column; align-items:center; gap:4px;">
                  <label style="font-size:0.75rem; font-weight:600; color:#555;">{{ $v->variant_name }}</label>
                  <input type="number" min="0" step="1" placeholder="0"
                    class="burrata-input"
                    data-variant-id="{{ $v->id }}"
                    data-variant-name="{{ $v->variant_name }}"
                    data-weight="{{ $v->weight_grams }}"
                    style="width:70px; padding:6px; border-radius:8px; border:1.5px solid #ccc; text-align:center; font-size:0.9rem;"
                    oninput="calcTotal('burrata')" />
                  <span style="font-size:0.7rem; color:#aaa;">pcs</span>
                </div>
                @endforeach
              </div>
            </div>

            {{-- STRACCIATELLA GRID --}}
            <div style="margin-bottom:1.5rem;">
              <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem;">
                <span style="font-weight:700; color:#1a6b47; font-size:0.95rem;"><i class="fas fa-cheese"></i> Stracciatella</span>
                <span id="straccTotal" style="font-size:0.8rem; color:#888;"></span>
              </div>
              <div style="display:flex; flex-wrap:wrap; gap:0.75rem;">
                @foreach($variants['Stracciatella'] ?? [] as $v)
                <div style="display:flex; flex-direction:column; align-items:center; gap:4px;">
                  <label style="font-size:0.75rem; font-weight:600; color:#555;">{{ $v->variant_name }}</label>
                  <input type="number" min="0" step="1" placeholder="0"
                    class="stracc-input"
                    data-variant-id="{{ $v->id }}"
                    data-variant-name="{{ $v->variant_name }}"
                    data-weight="{{ $v->weight_grams }}"
                    style="width:70px; padding:6px; border-radius:8px; border:1.5px solid #ccc; text-align:center; font-size:0.9rem;"
                    oninput="calcTotal('stracc')" />
                  <span style="font-size:0.7rem; color:#aaa;">pcs</span>
                </div>
                @endforeach
              </div>
            </div>

          {{-- CHERRY MOZZARELLA GRID --}}
            <div style="margin-bottom:1.5rem;">
              <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem;">
                <span style="font-weight:700; color:#1a6b47; font-size:0.95rem;"><i class="fas fa-cheese"></i> Cherry Mozzarella</span>
                <span id="cherryTotal" style="font-size:0.8rem; color:#888;"></span>
              </div>
              <div style="display:flex; flex-wrap:wrap; gap:0.75rem;">
                @foreach($variants['Cherry Mozzarella'] ?? [] as $v)
                <div style="display:flex; flex-direction:column; align-items:center; gap:4px;">
                  <label style="font-size:0.75rem; font-weight:600; color:#555;">{{ $v->variant_name }}</label>
                  <input type="number" min="0" step="1" placeholder="0"
                    class="cherry-input"
                    data-variant-id="{{ $v->id }}"
                    data-variant-name="{{ $v->variant_name }}"
                    data-weight="{{ $v->weight_grams }}"
                    style="width:70px; padding:6px; border-radius:8px; border:1.5px solid #ccc; text-align:center; font-size:0.9rem;"
                    oninput="calcTotal('cherry')" />
                  <span style="font-size:0.7rem; color:#aaa;">pcs</span>
                </div>
                @endforeach
              </div>
            </div>

            <hr style="margin:1rem 0;">

        {{-- MOZZARELLA LOG & PROVOLA --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
              <div class="form-group" style="margin:0;">
                <label style="font-weight:700; color:#1a6b47;"><i class="fas fa-cheese"></i> Mozzarella Log (kg)</label>
                <input type="number" min="0" step="0.01" placeholder="0"
                  id="mozzaKgInput"
                  data-variant-id="{{ $variants['Mozzarella Log'][0]->id ?? '' }}"
                  style="width:100%; padding:.6rem; border-radius:8px; border:1.5px solid #ccc;" />
              </div>
              <div class="form-group" style="margin:0;">
                <label style="font-weight:700; color:#1a6b47;"><i class="fas fa-cheese"></i> Provola (kg)</label>
                <input type="number" min="0" step="0.01" placeholder="0"
                  id="provolaKgInput"
                  data-variant-id="{{ $variants['Provola'][0]->id ?? '' }}"
                  style="width:100%; padding:.6rem; border-radius:8px; border:1.5px solid #ccc;" />
              </div>
            </div>

            <div id="orderValidationMsg" class="alert-message" style="display:none; margin-bottom:1rem;">
              <i class="fas fa-exclamation-circle"></i> Please select a client, date, and at least one product.
            </div>

            <div class="modal-buttons" style="margin-top:1rem;">
              <button type="button" class="btn-cancel" id="closeCreateOrder">Cancel</button>
              <button type="button" class="btn-primary" id="previewOrder">
                <i class="fas fa-eye"></i> Preview Ingredients
              </button>
            </div>
          </div>

          <div id="step2" style="display:none;">
            <p style="color:#666; margin-bottom:1rem; font-size:0.95rem;">Review ingredients needed. Green = sufficient, Red = insufficient.</p>
          <div class="table-container" style="margin-top:0; box-shadow:none; padding:0; overflow-x:hidden;">
              <table>
              <thead><tr><th>Ingredient</th><th>Needed</th><th>Available</th><th>Status</th></tr></thead>
                <tbody id="previewBody"></tbody>
              </table>
            </div>
            <div id="insufficientWarning" style="display:none; margin-top:1rem;" class="alert-message">
              <i class="fas fa-exclamation-circle"></i> Some ingredients are insufficient.
            </div>
            <div class="modal-buttons" style="margin-top:1.5rem;">
              <button type="button" class="btn-cancel" id="backToStep1"> Back</button>
            <button type="button" class="btn-primary" id="confirmOrder"><i class="fas fa-save"></i> Save Order</button>
            </div>
          </div>
        </div>
        </div>
      </div>  

      {{-- EDIT ORDER MODAL --}}
      <div id="editOrderModal" class="modal">
        <div class="modal-content" style="max-width:780px; padding:0; overflow:hidden;">
          <div style="background:linear-gradient(135deg,#0e472d,#1a6b47); padding:1.5rem 2rem; display:flex; align-items:center; gap:12px;">
            <i class="fas fa-pen" style="color:#fff; font-size:1.4rem;"></i>
            <h2 style="color:#fff; margin:0; border:none; padding:0; font-size:1.4rem;">Edit Order</h2>
          </div>
          <div style="padding:2rem; overflow-y:auto; max-height:80vh;">
          <form id="editOrderForm" method="POST" action="">
            @csrf @method('PUT')
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
              <div class="form-group" style="margin:0; grid-column:span 2;">
            <label>Client</label>
                <p id="editClientNameDisplay" style="padding:.6rem; font-weight:600; color:#333;"></p>
                <input type="hidden" name="client_name" id="editClientName" />
                <input type="hidden" name="client_id" id="editClientId">
              </div>
              <div class="form-group" style="margin:0;">
                <label>Order Date <span style="color:red;">*</span></label>
                <input type="date" name="order_date" id="editOrderDate" required style="width:100%; padding:.6rem; border-radius:8px; border:1.5px solid #ccc;" />
              </div>
              <div class="form-group" style="margin:0;">
                <label>Notes</label>
                <input type="text" name="notes" id="editOrderNotes" style="width:100%; padding:.6rem; border-radius:8px; border:1.5px solid #ccc;" />
              </div>
            </div>

            <hr style="margin:1rem 0;">
            <p style="color:#666; margin-bottom:1rem; font-size:0.95rem;">Edit quantities for this order:</p>

            {{-- BURRATA GRID --}}
            <div style="margin-bottom:1.5rem;">
              <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem;">
                <span style="font-weight:700; color:#1a6b47; font-size:0.95rem;"><i class="fas fa-cheese"></i> Burrata</span>
                <span id="editBurrataTotal" style="font-size:0.8rem; color:#888;"></span>
              </div>
              <div style="display:flex; flex-wrap:wrap; gap:0.75rem;">
                @foreach($variants['Burrata'] ?? [] as $v)
                <div style="display:flex; flex-direction:column; align-items:center; gap:4px;">
                  <label style="font-size:0.75rem; font-weight:600; color:#555;">{{ $v->variant_name }}</label>
                  <input type="number" min="0" step="1" placeholder="0"
                    class="edit-burrata-input"
                    data-variant-id="{{ $v->id }}"
                    data-variant-name="{{ $v->variant_name }}"
                    data-weight="{{ $v->weight_grams }}"
                    style="width:70px; padding:6px; border-radius:8px; border:1.5px solid #ccc; text-align:center; font-size:0.9rem;"
                    oninput="calcEditTotal('burrata')" />
                  <span style="font-size:0.7rem; color:#aaa;">pcs</span>
                </div>
                @endforeach
              </div>
            </div>

            {{-- STRACCIATELLA GRID --}}
            <div style="margin-bottom:1.5rem;">
              <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem;">
                <span style="font-weight:700; color:#1a6b47; font-size:0.95rem;"><i class="fas fa-cheese"></i> Stracciatella</span>
                <span id="editStraccTotal" style="font-size:0.8rem; color:#888;"></span>
              </div>
              <div style="display:flex; flex-wrap:wrap; gap:0.75rem;">
                @foreach($variants['Stracciatella'] ?? [] as $v)
                <div style="display:flex; flex-direction:column; align-items:center; gap:4px;">
                  <label style="font-size:0.75rem; font-weight:600; color:#555;">{{ $v->variant_name }}</label>
                  <input type="number" min="0" step="1" placeholder="0"
                    class="edit-stracc-input"
                    data-variant-id="{{ $v->id }}"
                    data-variant-name="{{ $v->variant_name }}"
                    data-weight="{{ $v->weight_grams }}"
                    style="width:70px; padding:6px; border-radius:8px; border:1.5px solid #ccc; text-align:center; font-size:0.9rem;"
                    oninput="calcEditTotal('stracc')" />
                  <span style="font-size:0.7rem; color:#aaa;">pcs</span>
                </div>
                @endforeach
              </div>
            </div>

            {{-- CHERRY MOZZARELLA GRID --}}
            <div style="margin-bottom:1.5rem;">
              <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem;">
                <span style="font-weight:700; color:#1a6b47; font-size:0.95rem;"><i class="fas fa-cheese"></i> Cherry Mozzarella</span>
                <span id="editCherryTotal" style="font-size:0.8rem; color:#888;"></span>
              </div>
              <div style="display:flex; flex-wrap:wrap; gap:0.75rem;">
                @foreach($variants['Cherry Mozzarella'] ?? [] as $v)
                <div style="display:flex; flex-direction:column; align-items:center; gap:4px;">
                  <label style="font-size:0.75rem; font-weight:600; color:#555;">{{ $v->variant_name }}</label>
                  <input type="number" min="0" step="1" placeholder="0"
                    class="edit-cherry-input"
                    data-variant-id="{{ $v->id }}"
                    data-variant-name="{{ $v->variant_name }}"
                    data-weight="{{ $v->weight_grams }}"
                    style="width:70px; padding:6px; border-radius:8px; border:1.5px solid #ccc; text-align:center; font-size:0.9rem;"
                    oninput="calcEditTotal('cherry')" />
                  <span style="font-size:0.7rem; color:#aaa;">pcs</span>
                </div>
                @endforeach
              </div>
            </div>

            <hr style="margin:1rem 0;">

            {{-- MOZZARELLA LOG & PROVOLA --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
              <div class="form-group" style="margin:0;">
                <label style="font-weight:700; color:#1a6b47;"><i class="fas fa-cheese"></i> Mozzarella Log (kg)</label>
                <input type="number" min="0" step="0.01" placeholder="0"
                  id="editMozzaKgInput"
                  data-variant-id="{{ $variants['Mozzarella Log'][0]->id ?? '' }}"
                  style="width:100%; padding:.6rem; border-radius:8px; border:1.5px solid #ccc;" />
              </div>
              <div class="form-group" style="margin:0;">
                <label style="font-weight:700; color:#1a6b47;"><i class="fas fa-cheese"></i> Provola (kg)</label>
                <input type="number" min="0" step="0.01" placeholder="0"
                  id="editProvolaKgInput"
                  data-variant-id="{{ $variants['Provola'][0]->id ?? '' }}"
                  style="width:100%; padding:.6rem; border-radius:8px; border:1.5px solid #ccc;" />
              </div>
            </div>

            {{-- HIDDEN ITEMS --}}
            <div id="editOrderHiddenItems"></div>

            <div class="modal-buttons" style="margin-top:1rem;">
              <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Changes</button>
              <button type="button" class="btn-cancel" id="closeEditOrder">Cancel</button>
            </div>
          </form>
          </div>
        </div>
      </div>

    {{-- CONFIRM ORDER PREVIEW MODAL --}}
      <div id="confirmPreviewModal" class="modal">
      <div class="modal-content" style="max-width:750px; padding:0; overflow:hidden;">
          <div style="background:linear-gradient(135deg,#0e472d,#1a6b47); padding:1.25rem 1.5rem; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle" style="color:#fff; font-size:1.2rem;"></i>
            <h2 style="color:#fff; margin:0; border:none; padding:0; font-size:1.2rem;">Confirm Order</h2>
          </div>
          <div style="padding:1.5rem;">
            <p style="color:#666; margin-bottom:1rem; font-size:0.9rem;">Review ingredients that will be deducted from inventory:</p>
          <div style="border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
              <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
    <tr style="background:#1a6b47;">
      <th style="text-align:left; padding:10px 14px; color:#ffffff; font-weight:600; font-size:0.8rem; letter-spacing:0.05em; border-bottom:1px solid #e5e7eb;">INGREDIENT</th>
      <th style="text-align:right; padding:10px 14px; color:#ffffff; font-weight:600; font-size:0.8rem; letter-spacing:0.05em; border-bottom:1px solid #e5e7eb;">NEEDED</th>
      <th style="text-align:right; padding:10px 14px; color:#ffffff; font-weight:600; font-size:0.8rem; letter-spacing:0.05em; border-bottom:1px solid #e5e7eb;">AVAILABLE</th>
      <th style="text-align:right; padding:10px 14px; color:#ffffff; font-weight:600; font-size:0.8rem; letter-spacing:0.05em; border-bottom:1px solid #e5e7eb;">UNIT</th>
      <th style="text-align:center; padding:10px 14px; color:#ffffff; font-weight:600; font-size:0.8rem; letter-spacing:0.05em; border-bottom:1px solid #e5e7eb; min-width:100px;">STATUS</th>
    </tr>
  </thead>
                <tbody id="confirmPreviewBody"></tbody>
              </table>
            </div>
            <div id="confirmInsufficientWarning" style="display:none; margin-top:1rem;" class="alert-message">
              <i class="fas fa-exclamation-circle"></i> Some ingredients are insufficient. Cannot confirm.
            </div>
            <div class="modal-buttons" style="margin-top:1.5rem;">
              <button type="button" class="btn-cancel" id="closeConfirmPreview">Cancel</button>
              <form id="confirmStatusForm" method="POST" action="" style="display:inline;">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="Confirmed">
                <button type="button" id="confirmStatusBtn" class="btn-primary"><i class="fas fa-check"></i> Confirm & Deduct</button>
              </form>
            </div>
          </div>
        </div>
      </div>

      {{-- ADD CLIENT MODAL --}}
      <div id="addClientModal" class="modal">
        <div class="modal-content" style="max-width:500px;">
          <h2><i class="fas fa-user-plus"></i> Add Client</h2>
          <form method="POST" action="{{ route('clients.store') }}" class="form-grid">
            @csrf
            <div class="form-group">
              <label>Client Name <span style="color:red;">*</span></label>
              <input type="text" name="name" required placeholder="e.g. Juan dela Cruz" />
            </div>
            <div class="form-group">
              <label>Contact Person</label>
              <input type="text" name="contact_person" placeholder="Optional" />
            </div>
            <div class="form-group">
              <label>Phone</label>
              <input type="text" name="phone" placeholder="Optional" />
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" placeholder="Optional" />
            </div>
            <div class="form-group">
              <label>Address</label>
              <textarea name="address" placeholder="Optional" style="width:100%; padding:.6rem; border-radius:8px; border:1.5px solid #ccc;"></textarea>
            </div>
            <div class="modal-buttons">
              <button type="button" class="btn-cancel" id="closeAddClient">Cancel</button>
              <button type="submit" class="btn-primary"><i class="fas fa-plus"></i> Add Client</button>
            </div>
          </form>
        </div>
      </div>

      {{-- EDIT CLIENT MODAL --}}
      <div id="editClientModal" class="modal">
        <div class="modal-content" style="max-width:500px;">
          <h2><i class="fas fa-pen"></i> Edit Client</h2>
          <form id="editClientForm" method="POST" action="" class="form-grid">
            @csrf @method('PUT')
            <div class="form-group">
              <label>Client Name <span style="color:red;">*</span></label>
              <input type="text" name="name" id="editClientNameField" required />
            </div>
            <div class="form-group">
              <label>Contact Person</label>
              <input type="text" name="contact_person" id="editContactPerson" />
            </div>
            <div class="form-group">
              <label>Phone</label>
              <input type="text" name="phone" id="editPhone" />
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" id="editEmail" />
            </div>
            <div class="form-group">
              <label>Address</label>
              <textarea name="address" id="editAddress" style="width:100%; padding:.6rem; border-radius:8px; border:1.5px solid #ccc;"></textarea>
            </div>
            <div class="modal-buttons">
              <button type="button" class="btn-cancel" id="closeEditClient">Cancel</button>
              <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            </div>
          </form>
        </div>
      </div>

      {{-- ARCHIVE ORDER CONFIRMATION MODAL --}}
  <div id="archiveOrderModal" class="modal">
    <div class="modal-content" style="max-width:420px; text-align:center;">
    <h2 style="margin-bottom:1rem;">Confirm Archive</h2>
      <p style="color:#555; margin-bottom:1.5rem;">Are you sure you want to archive this order? You can view it later from the Archived Orders tab.</p>
      <div class="modal-buttons" style="justify-content:center;">
        <button type="button" class="btn-cancel" id="closeArchiveOrder">Cancel</button>
        <form id="archiveOrderForm" method="POST" action="" style="display:inline;">
          @csrf
          <button type="submit" class="btn-primary" style="background:#e67e22; border-color:#e67e22;">
            <i class="fas fa-archive"></i> Archive
          </button>
        </form>
      </div>
    </div>
  </div>

      {{-- HIDDEN CONFIRM FORM --}}
  <form id="confirmOrderForm" action="{{ route('orders.store') }}" method="POST" style="display:none;">
        @csrf
        <div id="confirmOrderInputs"></div>
      </form>

      @endpush

      @push('scripts')
      <script>
      const variantsData = @json($variants);

      document.addEventListener('DOMContentLoaded', () => {
        const openModal  = (m) => { m?.classList.add('active'); document.body.classList.add('modal-open'); };
        const closeModal = (m) => { m?.classList.remove('active'); document.body.classList.remove('modal-open'); };

        setTimeout(() => document.getElementById('successAlert')?.remove(), 4000);
        setTimeout(() => document.getElementById('errorAlert')?.remove(), 5000);

        // Client select auto-fill name
        document.getElementById('clientSelect')?.addEventListener('change', function() {
          const selected = this.options[this.selectedIndex];
          const nameInput = document.getElementById('clientNameInput');
          if (this.value === 'walk-in') {
            nameInput.style.display = 'block';
            nameInput.value = '';
            nameInput.focus();
          } else if (this.value) {
            nameInput.style.display = 'none';
            nameInput.value = selected.dataset.name;
          } else {
            nameInput.style.display = 'none';
            nameInput.value = '';
          }
        });

        // CALC TOTALS
    window.calcTotal = function(product) {
          const cls    = product === 'burrata' ? '.burrata-input' : product === 'stracc' ? '.stracc-input' : '.cherry-input';
          const spanId = product === 'burrata' ? 'burrataTotal' : product === 'stracc' ? 'straccTotal' : 'cherryTotal';
          let totalKg  = 0;
          document.querySelectorAll(cls).forEach(inp => {
            const pcs    = parseFloat(inp.value) || 0;
            const weight = parseFloat(inp.dataset.weight) || 0;
            totalKg += (pcs * weight) / 1000;
          });
          const span = document.getElementById(spanId);
          if (span) span.textContent = totalKg > 0 ? `— Total: ${totalKg.toFixed(3)} kg` : '';
        };

        function buildRow(product = '', variantId = '', qty = '') {
          const div = document.createElement('div');
          div.className = 'order-item-row';
          div.style.cssText = 'display:grid; grid-template-columns:1.5fr 1.5fr 1fr auto; gap:1rem; margin-bottom:1rem;';

          const products = Object.keys(variantsData);
          let productOptions = '<option value="" disabled ' + (!product ? 'selected' : '') + '>Select product</option>';
          products.forEach(p => productOptions += `<option value="${p}" ${p === product ? 'selected' : ''}>${p}</option>`);

          let variantOptions = '<option value="" disabled selected>Select product first</option>';
          if (product && variantsData[product]) {
            variantOptions = '<option value="" disabled>Select variant</option>';
            variantsData[product].forEach(v => {
              variantOptions += `<option value="${v.id}" data-weight="${v.weight_grams}" ${v.id == variantId ? 'selected' : ''}>${v.variant_name} (${v.weight_grams}g)</option>`;
            });
          }

          div.innerHTML = `
            <div class="form-group" style="margin:0;">
              <label>Cheese Product</label>
              <select class="product-select" required>${productOptions}</select>
            </div>
            <div class="form-group" style="margin:0;">
              <label>Variant</label>
              <select class="variant-select" required ${!product ? 'disabled' : ''}>${variantOptions}</select>
            </div>
            <div class="form-group" style="margin:0;">
              <label>Qty (pcs)</label>
              <input type="number" class="quantity-input" min="1" step="1" placeholder="e.g. 20" value="${qty}" required />
            </div>
            <div style="display:flex; align-items:flex-end;">
              <button type="button" class="btn-cancel remove-row" style="padding:10px 14px;"><i class="fas fa-times"></i></button>
            </div>
          `;

          div.querySelector('.product-select').addEventListener('change', function() {
            const variantSelect = div.querySelector('.variant-select');
            const p = this.value;
            const variants = variantsData[p] || [];
            variantSelect.innerHTML = '<option value="" disabled selected>Select variant</option>';
            variants.forEach(v => {
              variantSelect.innerHTML += `<option value="${v.id}" data-weight="${v.weight_grams}">${v.variant_name} (${v.weight_grams}g)</option>`;
            });
            variantSelect.disabled = false;
          });

          return div;
        }

        function updateRemoveButtons(container) {
          const rows = container.querySelectorAll('.order-item-row');
          rows.forEach(row => row.querySelector('.remove-row').disabled = rows.length === 1);
        }

        function initContainer(container) {
          container.addEventListener('click', (e) => {
            if (e.target.closest('.remove-row')) {
              e.target.closest('.order-item-row').remove();
              updateRemoveButtons(container);
            }
          });
        }

        /// CREATE ORDER
        const createContainer = document.getElementById('orderItems');

      document.getElementById('openCreateOrder')?.addEventListener('click', () => {
          document.getElementById('clientSelect').value = '';
          document.getElementById('clientNameInput').value = '';
          document.getElementById('clientNameInput').style.display = 'none';
          document.getElementById('orderDateInput').value = '{{ now()->format("Y-m-d") }}';
          document.getElementById('orderNotesInput').value = '';
        document.querySelectorAll('.burrata-input, .stracc-input, .cherry-input').forEach(inp => inp.value = '');
          document.getElementById('mozzaKgInput').value = '';
          document.getElementById('provolaKgInput').value = '';
          document.getElementById('burrataTotal').textContent = '';
          document.getElementById('straccTotal').textContent = '';
          document.getElementById('cherryTotal').textContent = '';
          document.getElementById('step1').style.display = 'block';
          document.getElementById('step2').style.display = 'none';
          openModal(document.getElementById('createOrderModal'));
        });

        document.getElementById('closeCreateOrder')?.addEventListener('click', () => closeModal(document.getElementById('createOrderModal')));
    document.getElementById('previewOrder')?.addEventListener('click', async () => {
          const clientSelect = document.getElementById('clientSelect');
        const clientName = clientSelect.value === 'walk-in'
    ? document.getElementById('clientNameInput').value
    : (clientSelect.value && clientSelect.value !== ''
      ? (clientSelect.options[clientSelect.selectedIndex]?.dataset?.name || clientSelect.options[clientSelect.selectedIndex]?.text || '')
      : '');
          const clientId  = clientSelect.value;
          const orderDate = document.getElementById('orderDateInput').value;
          const items = [];

          document.querySelectorAll('.burrata-input').forEach(inp => {
            const pcs = parseFloat(inp.value) || 0;
            if (pcs > 0) items.push({ product: 'Burrata', variant_id: inp.dataset.variantId, quantity_pcs: pcs });
          });
          document.querySelectorAll('.stracc-input').forEach(inp => {
            const pcs = parseFloat(inp.value) || 0;
            if (pcs > 0) items.push({ product: 'Stracciatella', variant_id: inp.dataset.variantId, quantity_pcs: pcs });
          });

          document.querySelectorAll('.cherry-input').forEach(inp => {
            const pcs = parseFloat(inp.value) || 0;
            if (pcs > 0) items.push({ product: 'Cherry Mozzarella', variant_id: inp.dataset.variantId, quantity_pcs: pcs });
          });

          const mozzaKg = parseFloat(document.getElementById('mozzaKgInput').value) || 0;
          const mozzaVariantId = document.getElementById('mozzaKgInput').dataset.variantId;
          if (mozzaKg > 0 && mozzaVariantId) items.push({ product: 'Mozzarella Log', variant_id: mozzaVariantId, quantity_pcs: mozzaKg });
          const provolaKg = parseFloat(document.getElementById('provolaKgInput').value) || 0;
          const provolaVariantId = document.getElementById('provolaKgInput').dataset.variantId;
          if (provolaKg > 0 && provolaVariantId) items.push({ product: 'Provola', variant_id: provolaVariantId, quantity_pcs: provolaKg });

          const validationMsg = document.getElementById('orderValidationMsg');
  if (!clientName) {
    if (validationMsg) { validationMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> Please select a client.'; validationMsg.style.display = 'flex'; }
    return;
  }
  if (!orderDate) {
    if (validationMsg) { validationMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> Please select an order date.'; validationMsg.style.display = 'flex'; }
    return;
  }
  if (items.length === 0) {
    if (validationMsg) { validationMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> Please add at least one product.'; validationMsg.style.display = 'flex'; }
    return;
  }
  if (validationMsg) validationMsg.style.display = 'none';

          try {
            const response = await fetch('{{ route("orders.preview") }}', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
              body: JSON.stringify({ items }),
            });
            const data = await response.json();
            const tbody = document.getElementById('previewBody');
            tbody.innerHTML = '';
            data.preview.forEach(row => {
    const fmt = (v) => parseFloat(parseFloat(v).toFixed(3)).toString();

  const ingredientConfig = {
      'Cream':        { neededUnit: 'pcs',    availableUnit: 'L',  toAvailable: (v) => fmt(v * 0.0625) },
      'Iodized Salt': { neededUnit: 'scoops', availableUnit: 'kg', toAvailable: (v) => fmt(v * 0.006)  }
  };

  const config = ingredientConfig[row.ingredient];
  const ok = row.available >= row.needed;

  const neededText    = config ? `${row.needed} ${config.neededUnit}` : `${fmt(row.needed)} ${row.unit}`;
  const availableText = config ? `${config.toAvailable(row.available)} ${config.availableUnit}` : `${fmt(row.available)} ${row.unit}`;

  const [nNum, nUnit] = neededText.split(' ');
  const [aNum, aUnit] = availableText.split(' ');

  tbody.innerHTML += `<tr style="border-bottom:1px solid #f3f4f6;">
    <td style="padding:10px 14px;">${row.ingredient}</td>
    <td style="text-align:right; padding:10px 14px;"><span class="value">${nNum}</span> <span class="unit">${nUnit}</span></td>
    <td style="text-align:right; padding:10px 14px;"><span class="value">${aNum}</span> <span class="unit">${aUnit}</span></td>
    <td style="text-align:center; padding:10px 14px;"><span class="status-tag ${ok ? 'active' : 'inactive'}">${ok ? 'OK' : 'Insufficient'}</span></td>
  </tr>`;

            });
           const insuffWarn = document.getElementById('insufficientWarning');
if (insuffWarn) insuffWarn.style.display = data.insufficient > 0 ? 'flex' : 'none';
          const confirmBtn = document.getElementById('confirmOrder');
  if (confirmBtn) confirmBtn.disabled = false;
            window._orderItems = items;
            window._clientName = clientName;
            window._clientId   = clientId;
            document.getElementById('step1').style.display = 'none';
            document.getElementById('step2').style.display = 'block';
        } catch (err) {
            alert(err.message);
          }
        });

        document.getElementById('backToStep1')?.addEventListener('click', () => {
          document.getElementById('step1').style.display = 'block';
          document.getElementById('step2').style.display = 'none';
        });

      document.getElementById('confirmOrder')?.addEventListener('click', () => {
          const items      = window._orderItems;
          const clientId   = window._clientId;
          const clientName = window._clientName;
          const orderDate  = document.getElementById('orderDateInput').value;
          const notes      = document.getElementById('orderNotesInput').value;
          const inputsDiv  = document.getElementById('confirmOrderInputs');
          inputsDiv.innerHTML = '';

          if (clientId && clientId !== 'walk-in') {
            inputsDiv.innerHTML += `<input type="hidden" name="client_id" value="${clientId}">`;
          }
          inputsDiv.innerHTML += `<input type="hidden" name="client_name" value="${clientName}">`;
          inputsDiv.innerHTML += `<input type="hidden" name="order_date" value="${orderDate}">`;
          inputsDiv.innerHTML += `<input type="hidden" name="notes" value="${notes}">`;

          items.forEach((item, i) => {
            inputsDiv.innerHTML += `
              <input type="hidden" name="items[${i}][product]"      value="${item.product}">
              <input type="hidden" name="items[${i}][variant_id]"   value="${item.variant_id}">
              <input type="hidden" name="items[${i}][quantity_pcs]" value="${item.quantity_pcs}">
            `;
          });
          document.getElementById('confirmOrderForm').submit();
        });

      // EDIT ORDER
        window.calcEditTotal = function(product) {
          const cls    = product === 'burrata' ? '.edit-burrata-input' : product === 'stracc' ? '.edit-stracc-input' : '.edit-cherry-input';
          const spanId = product === 'burrata' ? 'editBurrataTotal' : product === 'stracc' ? 'editStraccTotal' : 'editCherryTotal';
          let totalKg  = 0;
          document.querySelectorAll(cls).forEach(inp => {
            const pcs    = parseFloat(inp.value) || 0;
            const weight = parseFloat(inp.dataset.weight) || 0;
            totalKg += (pcs * weight) / 1000;
          });
          const span = document.getElementById(spanId);
          if (span) span.textContent = totalKg > 0 ? `— Total: ${totalKg.toFixed(3)} kg` : '';
        };

        document.getElementById('editOrderForm')?.addEventListener('submit', function(e) {
          e.preventDefault();
          const hiddenDiv = document.getElementById('editOrderHiddenItems');
          hiddenDiv.innerHTML = '';
          let i = 0;

          document.querySelectorAll('.edit-burrata-input').forEach(inp => {
            const pcs = parseFloat(inp.value) || 0;
            if (pcs > 0) {
              hiddenDiv.innerHTML += `<input type="hidden" name="items[${i}][product]" value="Burrata"><input type="hidden" name="items[${i}][variant_id]" value="${inp.dataset.variantId}"><input type="hidden" name="items[${i}][quantity_pcs]" value="${pcs}">`;
              i++;
            }
          });
          document.querySelectorAll('.edit-stracc-input').forEach(inp => {
            const pcs = parseFloat(inp.value) || 0;
            if (pcs > 0) {
              hiddenDiv.innerHTML += `<input type="hidden" name="items[${i}][product]" value="Stracciatella"><input type="hidden" name="items[${i}][variant_id]" value="${inp.dataset.variantId}"><input type="hidden" name="items[${i}][quantity_pcs]" value="${pcs}">`;
              i++;
            }
          });
          document.querySelectorAll('.edit-cherry-input').forEach(inp => {
            const pcs = parseFloat(inp.value) || 0;
            if (pcs > 0) {
              hiddenDiv.innerHTML += `<input type="hidden" name="items[${i}][product]" value="Cherry Mozzarella"><input type="hidden" name="items[${i}][variant_id]" value="${inp.dataset.variantId}"><input type="hidden" name="items[${i}][quantity_pcs]" value="${pcs}">`;
              i++;
            }
          });
          const mozzaKg = parseFloat(document.getElementById('editMozzaKgInput').value) || 0;
          const mozzaVariantId = document.getElementById('editMozzaKgInput').dataset.variantId;
          if (mozzaKg > 0 && mozzaVariantId) {
            hiddenDiv.innerHTML += `<input type="hidden" name="items[${i}][product]" value="Mozzarella Log"><input type="hidden" name="items[${i}][variant_id]" value="${mozzaVariantId}"><input type="hidden" name="items[${i}][quantity_pcs]" value="${mozzaKg}">`;
            i++;
          }
          const provolaKg = parseFloat(document.getElementById('editProvolaKgInput').value) || 0;
          const provolaVariantId = document.getElementById('editProvolaKgInput').dataset.variantId;
          if (provolaKg > 0 && provolaVariantId) {
            hiddenDiv.innerHTML += `<input type="hidden" name="items[${i}][product]" value="Provola"><input type="hidden" name="items[${i}][variant_id]" value="${provolaVariantId}"><input type="hidden" name="items[${i}][quantity_pcs]" value="${provolaKg}">`;
            i++;
          }

          if (i === 0) { alert('Please add at least one product.'); return; }
          this.submit();
        });

        document.querySelectorAll('.edit-order-btn').forEach(btn => {
          btn.addEventListener('click', () => {
            const items = JSON.parse(btn.dataset.items);
            document.getElementById('editOrderForm').action = `/orders/${btn.dataset.id}`;
          document.getElementById('editClientName').value  = btn.dataset.clientName;
            document.getElementById('editClientNameDisplay').textContent = btn.dataset.clientName;
            document.getElementById('editClientId').value    = btn.dataset.clientId;
            document.getElementById('editOrderDate').value   = btn.dataset.orderDate;
            document.getElementById('editOrderNotes').value  = btn.dataset.notes;

            // Reset all inputs
            document.querySelectorAll('.edit-burrata-input, .edit-stracc-input, .edit-cherry-input').forEach(inp => inp.value = '');
            document.getElementById('editMozzaKgInput').value = '';
            document.getElementById('editProvolaKgInput').value = '';
            document.getElementById('editBurrataTotal').textContent = '';
            document.getElementById('editStraccTotal').textContent = '';
            document.getElementById('editCherryTotal').textContent = '';

          // Pre-fill existing quantities
            items.forEach(item => {
              if (item.cheese_product === 'Mozzarella Log') {
                document.getElementById('editMozzaKgInput').value = item.quantity_pieces;
              } else if (item.cheese_product === 'Provola') {
                document.getElementById('editProvolaKgInput').value = item.quantity_pieces;
              } else {
              const cls = item.cheese_product === 'Burrata' ? '.edit-burrata-input' :
              item.cheese_product === 'Stracciatella' ? '.edit-stracc-input' : '.edit-cherry-input';
  document.querySelectorAll(cls).forEach(inp => {
    if (inp.dataset.variantName == item.variant_name) inp.value = item.quantity_pieces;
  });
              }
            });

            calcEditTotal('burrata');
            calcEditTotal('stracc');
            calcEditTotal('cherry');
            openModal(document.getElementById('editOrderModal'));
          });
        });

        document.getElementById('closeEditOrder')?.addEventListener('click', () => closeModal(document.getElementById('editOrderModal')));

        // CONFIRM ORDER (from table button)
        document.querySelectorAll('.confirm-order-btn').forEach(btn => {
          btn.addEventListener('click', async () => {
            const items = JSON.parse(btn.dataset.items);
            const orderId = btn.dataset.id;

            try {
            const itemsForPreview = items.map(item => ({
                product: item.cheese_product,
                variant_id: item.weight_grams,
                quantity_pcs: item.quantity_pieces,
                total_kg: item.total_kg,
              }));

              const response = await fetch('{{ route("orders.preview") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ items: itemsForPreview }),
              });
              const data = await response.json();
              const tbody = document.getElementById('confirmPreviewBody');
              tbody.innerHTML = '';
              data.preview.forEach(row => {
        const fmt = (v) => parseFloat(parseFloat(v).toFixed(3)).toString();

  const ingredientConfig = {
      'Cream':        { neededUnit: 'pcs',    availableUnit: 'L',  toAvailable: (v) => fmt(v * 0.0625) },
      'Iodized Salt': { neededUnit: 'scoops', availableUnit: 'kg', toAvailable: (v) => fmt(v * 0.006)  }
  };

  const config = ingredientConfig[row.ingredient];
  const ok = row.available >= row.needed;

  const neededText    = config ? `${row.needed} ${config.neededUnit}` : `${fmt(row.needed)} ${row.unit}`;
  const availableText = config ? `${config.toAvailable(row.available)} ${config.availableUnit}` : `${fmt(row.available)} ${row.unit}`;

  const [nNum, nUnit] = neededText.split(' ');
  const [aNum, aUnit] = availableText.split(' ');

  tbody.innerHTML += `<tr style="border-bottom:1px solid #f3f4f6;">
    <td style="padding:10px 14px;">${row.ingredient}</td>
    <td style="text-align:right; padding:10px 14px;"><span class="value">${nNum}</span> <span class="unit">${nUnit}</span></td>
    <td style="text-align:right; padding:10px 14px;"><span class="value">${aNum}</span> <span class="unit">${aUnit}</span></td>
    <td style="text-align:center; padding:10px 14px;"><span class="status-tag ${ok ? 'active' : 'inactive'}">${ok ? 'OK' : 'Insufficient'}</span></td>
  </tr>`;
              });
            document.getElementById('confirmInsufficientWarning').style.display = 'none';
            document.getElementById('confirmStatusBtn').disabled = false;
  document.getElementById('confirmStatusBtn').dataset.insufficient = data.insufficient;
              document.getElementById('confirmStatusForm').action = `/orders/${orderId}/status`;
              openModal(document.getElementById('confirmPreviewModal'));
            } catch (err) {
              alert('Something went wrong.');
            }
          });
        });

        document.getElementById('closeConfirmPreview')?.addEventListener('click', () => closeModal(document.getElementById('confirmPreviewModal')));

        document.getElementById('confirmStatusBtn')?.addEventListener('click', () => {
    const insufficient = parseInt(document.getElementById('confirmStatusBtn').dataset.insufficient) || 0;
    if (insufficient > 0) {
      document.getElementById('confirmInsufficientWarning').style.display = 'flex';
      return;
    }
    document.getElementById('confirmStatusForm').submit();
  }); 

        // ARCHIVE ORDER
        document.querySelectorAll('.archive-order-btn').forEach(btn => {
          btn.addEventListener('click', () => {
            document.getElementById('archiveOrderForm').action = btn.dataset.url;
            openModal(document.getElementById('archiveOrderModal'));
          });
        });
        document.getElementById('closeArchiveOrder')?.addEventListener('click', () => closeModal(document.getElementById('archiveOrderModal')));
        // CLIENT MODALS
        document.getElementById('openAddClient')?.addEventListener('click', () => openModal(document.getElementById('addClientModal')));
        document.getElementById('closeAddClient')?.addEventListener('click', () => closeModal(document.getElementById('addClientModal')));
        document.getElementById('closeEditClient')?.addEventListener('click', () => closeModal(document.getElementById('editClientModal')));

        document.querySelectorAll('.edit-client-btn').forEach(btn => {
          btn.addEventListener('click', () => {
            document.getElementById('editClientForm').action = `/clients/${btn.dataset.id}`;
            document.getElementById('editClientNameField').value = btn.dataset.name;
            document.getElementById('editContactPerson').value   = btn.dataset.contact;
            document.getElementById('editPhone').value           = btn.dataset.phone;
            document.getElementById('editEmail').value           = btn.dataset.email;
            document.getElementById('editAddress').value         = btn.dataset.address;
            openModal(document.getElementById('editClientModal'));
          });
        });

      // MAIN TAB SWITCHING
        window.switchMainTab = function(tab) {
          ['mainTabOrders','mainTabArchivedOrders','mainTabClients','mainTabArchivedClients'].forEach(id => {
            const el = document.getElementById(id); if (el) el.style.display = 'none';
          });
          const map = {'orders':'mainTabOrders','archived-orders':'mainTabArchivedOrders','clients':'mainTabClients','archived-clients':'mainTabArchivedClients'};
          const target = document.getElementById(map[tab]);
          if (target) target.style.display = 'block';
          document.querySelectorAll('.btn-tab[onclick^="switchMainTab"]').forEach(btn => btn.classList.remove('active'));
          document.querySelector(`.btn-tab[onclick="switchMainTab('${tab}')"]`).classList.add('active');
          document.querySelector('input[name="tab"]').value = tab;
          const createOrderBtn = document.getElementById('openCreateOrder');
  const addClientBtn = document.getElementById('openAddClient');
  if (createOrderBtn) createOrderBtn.style.display = tab === 'orders' ? 'inline-flex' : 'none';
  if (addClientBtn) addClientBtn.style.display = (tab === 'clients' || tab === 'archived-clients') ? 'inline-flex' : 'none';
        };    

        // CLIENT TAB SWITCHING
        window.switchClientTab = function(tab) {
          document.getElementById('activeClientsTable').style.display   = tab === 'active'   ? 'block' : 'none';
          document.getElementById('archivedClientsTable').style.display = tab === 'archived' ? 'block' : 'none';
          document.getElementById('tabActiveClients').classList.toggle('active',   tab === 'active');
          document.getElementById('tabArchivedClients').classList.toggle('active', tab === 'archived');
        };

      });
      </script>
      @endpush    