<?php
// customer_result.php - Manage customers via REST API (GET/POST/PUT/DELETE)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Customer Management</title>
    <style>
        :root {
            --bg: #f8f5f1;
            --card: #ffffff;
            --text: #2b2b2b;
            --muted: #6b7280;
            --border: #e6e1da;
            --primary: #7b5e45;
            --primary-600: #6c503a;
            --secondary: #6b7280;
            --secondary-600: #4b5563;
            --danger: #b91c1c;
            --danger-700: #991b1b;
            --success-bg: #ecfdf5;
            --success-border: #a7f3d0;
            --success-text: #065f46;
            --error-bg: #fee2e2;
            --error-border: #fecaca;
            --error-text: #7f1d1d;
            color-scheme: light dark;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0b0f15;
                --card: #0f1720;
                --text: #e5e7eb;
                --muted: #9aa3af;
                --border: #1f2937;
                --primary: #a98c6d;
                --primary-600: #92795e;
                --secondary: #4b5563;
                --secondary-600: #374151;
                --danger: #ef4444;
                --danger-700: #dc2626;
                --success-bg: #053026;
                --success-border: #065f46;
                --success-text: #a7f3d0;
                --error-bg: #2a0f0f;
                --error-border: #7f1d1d;
                --error-text: #fecaca;
            }
        }
        * { box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
            margin: 0; background: var(--bg); color: var(--text);
        }
        header {
            padding: 1rem 1.25rem; background: var(--card); border-bottom: 1px solid var(--border);
            position: sticky; top: 0; z-index: 10; backdrop-filter: blur(4px);
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
        .brand { display: flex; align-items: center; gap: 0.6rem; }
        .brand-logo { width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg, #c7b39a, #7b5e45); box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        .brand-title { margin: 0; font-size: 1.05rem; letter-spacing: 0.2px; }
        .toolbar { display: flex; gap: 0.5rem; align-items: center; }

        main { padding: 1.25rem; }
        .grid { display: grid; grid-template-columns: 1fr; gap: 1rem; align-items: start; }
        .card {
            background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 1rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        }
        .card h2 { margin: 0 0 0.6rem 0; font-size: 1.05rem; letter-spacing: 0.2px; }
        .card .sub { color: var(--muted); font-size: 0.92rem; margin-bottom: 0.8rem; }

        .btn { appearance: none; border: 0; border-radius: 10px; padding: 0.5rem 0.8rem; cursor: pointer; font-size: 0.92rem; transition: all .15s ease-in-out; }
        .btn:disabled { opacity: .6; cursor: not-allowed; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-600); }
        .btn-secondary { background: var(--secondary); color: #fff; }
        .btn-secondary:hover { background: var(--secondary-600); }
        .btn-ghost { background: transparent; color: var(--primary); border: 1px solid var(--primary); }
        .btn-ghost:hover { background: rgba(123,94,69,0.09); }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-danger:hover { background: var(--danger-700); }

        form .row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.75rem; }
        @media (max-width: 700px) { form .row { grid-template-columns: 1fr; } }
        label { display: block; font-size: 0.88rem; margin: 0.25rem 0 0.35rem; color: var(--muted); }
        input, select, textarea {
            width: 100%; padding: 0.55rem 0.65rem; border: 1px solid var(--border); border-radius: 10px; background: var(--card); color: inherit;
            outline: none; transition: box-shadow .15s ease, border-color .15s ease;
        }
        input:focus, select:focus, textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(123,94,69,0.15); }
        textarea { resize: vertical; }
        .actions { margin-top: 0.9rem; display: flex; gap: 0.5rem; }

        .msg { padding: 0.65rem 0.85rem; border-radius: 10px; margin-bottom: 0.75rem; display: none; border: 1px solid transparent; }
        .msg.ok { display: block; background: var(--success-bg); color: var(--success-text); border-color: var(--success-border); }
        .msg.err { display: block; background: var(--error-bg); color: var(--error-text); border-color: var(--error-border); }

        .table-wrap { overflow-x: auto; border-radius: 12px; border: 1px solid var(--border); background: var(--card); }
        table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.95rem; }
        thead th {
            position: sticky; top: 0; background: linear-gradient(0deg, var(--card), var(--card));
            text-align: left; border-bottom: 1px solid var(--border); padding: 0.6rem 0.7rem; white-space: nowrap; color: var(--muted);
        }
        tbody td { border-bottom: 1px solid var(--border); padding: 0.55rem 0.7rem; vertical-align: top; }
        tbody tr:nth-child(even) { background: rgba(0,0,0,0.015); }
        tbody tr:hover { background: rgba(123,94,69,0.06); }
        .nowrap { white-space: nowrap; }
        .actions-col { display: flex; gap: 0.4rem; }
    </style>
</head>
<body>
    <header>
        <div class="container header-row">
            <div class="brand">
                <div class="brand-logo" aria-hidden="true"></div>
                <h1 class="brand-title">Customer Management</h1>
            </div>
            <div class="toolbar">
                <a class="btn btn-secondary" href="index.html">Home</a>
                <a class="btn btn-secondary" href="menu.html">Menu</a>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <div class="grid">
                <section class="card">
                    <h2>Add Customer</h2>
                    <p class="sub">Create a new customer record. All fields are required.</p>
                    <div id="msg-add" class="msg"></div>
                    <form id="addForm" autocomplete="off">
                        <div class="row">
                            <div>
                                <label for="firstName">First Name</label>
                                <input type="text" id="firstName" name="firstName" required />
                            </div>
                            <div>
                                <label for="lastName">Last Name</label>
                                <input type="text" id="lastName" name="lastName" required />
                            </div>
                            <div>
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender" required>
                                    <option value="">-- Select --</option>
                                    <option>Male</option>
                                    <option>Female</option>
                                    <option>Other</option>
                                </select>
                            </div>
                            <div>
                                <label for="dob">Date of Birth</label>
                                <input type="date" id="dob" name="dob" required />
                            </div>
                            <div>
                                <label for="contact">Contact Number</label>
                                <input type="text" id="contact" name="contact" required pattern="\d{7,15}" />
                            </div>
                            <div>
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" required />
                            </div>
                            <div style="grid-column: 1 / -1;">
                                <label for="address">Address</label>
                                <textarea id="address" name="address" rows="2" required></textarea>
                            </div>
                            <div>
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" required minlength="6" />
                            </div>
                            <div>
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" required minlength="6" />
                            </div>
                        </div>
                        <div class="actions">
                            <button type="submit" class="btn btn-primary">Add</button>
                            <button type="reset" class="btn btn-secondary" id="resetAdd">Reset</button>
                        </div>
                    </form>
                </section>

                <section class="card">
                    <h2>Customers</h2>
                    <p class="sub">These rows reflect the current contents of customers.json.</p>
                    <div id="msg-table" class="msg"></div>
                    <div class="table-wrap">
                        <table id="customersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>First</th>
                                    <th>Last</th>
                                    <th>Gender</th>
                                    <th>Address</th>
                                    <th>Contact</th>
                                    <th>Email</th>
                                    <th>DOB</th>
                                    <th>Username</th>
                                    <th class="nowrap">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="customersBody">
                                <tr><td colspan="10" class="muted">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </main>
    <script>
    const apiBase = 'api/customers.php';

    const msgAdd = document.getElementById('msg-add');
    const msgTable = document.getElementById('msg-table');
    const addForm = document.getElementById('addForm');
    const tbody = document.getElementById('customersBody');

    function showMsg(el, text, ok=false) {
        el.textContent = text;
        el.className = 'msg ' + (ok ? 'ok' : 'err');
        el.style.display = text ? 'block' : 'none';
    }

    async function fetchJSON(url, options = {}) {
        const bust = (url.includes('?') ? '&' : '?') + '_ts=' + Date.now();
        const res = await fetch(url + bust, Object.assign({ headers: { 'Accept': 'application/json' }, cache: 'no-store' }, options));
        const text = await res.text();
        let data = null;
        try { data = text ? JSON.parse(text) : null; } catch (e) {}
        return { res, data, text };
    }

    function htmlesc(s){
        const d = document.createElement('div');
        d.innerText = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function renderRow(c) {
        const tr = document.createElement('tr');
        tr.dataset.id = c.id;
        tr.innerHTML = `
            <td class="nowrap">${htmlesc(c.id)}</td>
            <td>${htmlesc(c.firstName || '')}</td>
            <td>${htmlesc(c.lastName || '')}</td>
            <td>${htmlesc(c.gender || '')}</td>
            <td>${htmlesc(c.address || '')}</td>
            <td>${htmlesc(c.phone || '')}</td>
            <td>${htmlesc(c.email || '')}</td>
            <td>${htmlesc(c.dob || '')}</td>
            <td>${htmlesc(c.username || '')}</td>
            <td class="actions-col nowrap">
                <button data-action="edit" class="btn btn-ghost">Edit</button>
                <button data-action="delete" class="btn btn-danger">Delete</button>
            </td>
        `;
        return tr;
    }

    function toInputCell(value, type='text') {
        return `<input type="${type}" value="${htmlesc(value ?? '')}" style="width:100%">`;
    }

    function toSelectGender(value) {
        const opts = ['Male','Female','Other'];
        return `<select style="width:100%">${opts.map(o => `<option ${o===value?'selected':''}>${o}</option>`).join('')}</select>`;
    }

    function enterEdit(tr) {
        const tds = tr.querySelectorAll('td');
        tds[1].innerHTML = toInputCell(tds[1].innerText);
        tds[2].innerHTML = toInputCell(tds[2].innerText);
        tds[3].innerHTML = toSelectGender(tds[3].innerText);
        tds[4].innerHTML = `<textarea rows="2" style="width:100%">${htmlesc(tds[4].innerText)}</textarea>`;
        tds[5].innerHTML = toInputCell(tds[5].innerText, 'text');
        tds[6].innerHTML = toInputCell(tds[6].innerText, 'email');
        tds[7].innerHTML = toInputCell(tds[7].innerText, 'date');
        tds[8].innerHTML = toInputCell(tds[8].innerText, 'text');
        const actions = tds[9];
        actions.innerHTML = '';
        const saveBtn = document.createElement('button'); saveBtn.textContent = 'Save'; saveBtn.dataset.action = 'save'; saveBtn.className = 'btn btn-primary';
        const cancelBtn = document.createElement('button'); cancelBtn.textContent = 'Cancel'; cancelBtn.dataset.action = 'cancel'; cancelBtn.className = 'btn btn-secondary';
        const pwdInput = document.createElement('input'); pwdInput.type = 'password'; pwdInput.placeholder = 'New Password (optional)'; pwdInput.minLength = 6; pwdInput.style.marginLeft = '0.4rem'; pwdInput.style.border = '1px solid var(--border)'; pwdInput.style.borderRadius = '10px'; pwdInput.style.padding = '0.45rem 0.55rem'; pwdInput.style.background = 'var(--card)'; pwdInput.style.color = 'inherit';
        actions.append(saveBtn, cancelBtn, pwdInput);
    }

    function exitEdit(tr, original) {
        tr.replaceWith(renderRow(original));
    }

    async function loadCustomers() {
        tbody.innerHTML = '<tr><td colspan="10" class="muted">Loading...</td></tr>';
        const { res, data } = await fetchJSON(apiBase);
        if (!res.ok) {
            showMsg(msgTable, (data && data.error) ? data.error : 'Failed to load customers');
            tbody.innerHTML = '<tr><td colspan="10" class="muted">No data</td></tr>';
            return;
        }
        tbody.innerHTML = '';
        (data || []).forEach(c => tbody.appendChild(renderRow(c)));
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="muted">No customers yet.</td></tr>';
        }
    }

    addForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        showMsg(msgAdd, '');
        const payload = {
            firstName: addForm.firstName.value.trim(),
            lastName: addForm.lastName.value.trim(),
            gender: addForm.gender.value,
            address: addForm.address.value.trim(),
            contact: addForm.contact.value.trim(),
            email: addForm.email.value.trim(),
            dob: addForm.dob.value,
            username: addForm.username.value.trim(),
            password: addForm.password.value
        };
        const { res, data } = await fetchJSON(apiBase, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        });
        if (res.status === 201) {
            showMsg(msgAdd, 'Customer added successfully', true);
            addForm.reset();
            loadCustomers();
        } else {
            let msg = 'Failed to add customer';
            if (data && data.errors) msg = data.errors.join(', ');
            else if (data && data.error) msg = data.error;
            showMsg(msgAdd, msg);
        }
    });

    tbody.addEventListener('click', async (e) => {
        const btn = e.target.closest('button');
        if (!btn) return;
        const tr = e.target.closest('tr');
        const id = parseInt(tr?.dataset?.id || '0', 10);
        if (!id) return;

        if (btn.dataset.action === 'edit') {
            const cells = tr.querySelectorAll('td');
            const original = {
                id: id,
                firstName: cells[1].innerText,
                lastName: cells[2].innerText,
                gender: cells[3].innerText,
                address: cells[4].innerText,
                phone: cells[5].innerText,
                email: cells[6].innerText,
                dob: cells[7].innerText,
                username: cells[8].innerText,
            };
            tr.dataset.original = JSON.stringify(original);
            enterEdit(tr);
        }
        else if (btn.dataset.action === 'cancel') {
            const original = JSON.parse(tr.dataset.original || '{}');
            exitEdit(tr, original);
        }
        else if (btn.dataset.action === 'save') {
            const tds = tr.querySelectorAll('td');
            const payload = {
                firstName: tds[1].querySelector('input')?.value?.trim() || '',
                lastName: tds[2].querySelector('input')?.value?.trim() || '',
                gender: tds[3].querySelector('select')?.value || '',
                address: tds[4].querySelector('textarea')?.value?.trim() || '',
                contact: tds[5].querySelector('input')?.value?.trim() || '',
                email: tds[6].querySelector('input')?.value?.trim() || '',
                dob: tds[7].querySelector('input')?.value || '',
                username: tds[8].querySelector('input')?.value?.trim() || '',
            };
            const pwd = tds[9].querySelector('input[type="password"]').value;
            if (pwd) payload.password = pwd;

            const { res, data } = await fetchJSON(`${apiBase}?id=${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            if (res.ok) {
                showMsg(msgTable, 'Customer updated', true);
                loadCustomers();
            } else {
                let msg = 'Failed to update customer';
                if (data && data.errors) msg = data.errors.join(', ');
                else if (data && data.error) msg = data.error;
                showMsg(msgTable, msg);
            }
        }
        else if (btn.dataset.action === 'delete') {
            if (!confirm('Delete this customer?')) return;
            const { res, data } = await fetchJSON(`${apiBase}?id=${id}`, { method: 'DELETE' });
            if (res.status === 204) {
                showMsg(msgTable, 'Customer deleted', true);
                loadCustomers();
            } else {
                showMsg(msgTable, (data && data.error) ? data.error : 'Failed to delete');
            }
        }
    });

    loadCustomers();
    </script>
</body>
</html>