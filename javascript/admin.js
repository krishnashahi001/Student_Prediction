// Admin page JavaScript
// Loads users, streams and handles filtering by stream.

async function fetchJSON(url) {
	const res = await fetch(url);
	if (!res.ok) throw new Error('Network response was not ok');
	return res.json();
}

function escapeHtml(s) { if (s == null) return ''; return String(s).replace(/[&<>"']/g, c => '&#' + c.charCodeAt(0) + ';'); }

async function loadUsers() {
	closeEditor();
	const tbody = document.querySelector('#usersTable tbody');
	if (!tbody) {
		// page doesn't have a users table, nothing to do
		return;
	}
	try {
		const data = await fetchJSON('backend/admin_api.php?action=list_users');
		tbody.innerHTML = '';
		if (!data.users || data.users.length === 0) {
			tbody.innerHTML = '<tr><td colspan="4">No users found.</td></tr>';
			return;
		}
		data.users.forEach(u => {
			const tr = document.createElement('tr');
			tr.innerHTML = `<td>${escapeHtml(u.id)}</td><td>${escapeHtml(u.name)}</td><td>${escapeHtml(u.email)}</td><td>${escapeHtml(u.contact || u.contactno || 'N/A')}</td><td><button class="btn-edit" onclick="editStudent('${escapeHtml(u.id)}')">Edit</button></td>`;
			tbody.appendChild(tr);
		});
	} catch (err) {
		console.error(err);
		tbody.innerHTML = '<tr><td colspan="4">Error loading users</td></tr>';
	}
}

async function loadStreams() {
	try {
		const data = await fetchJSON('backend/admin_api.php?action=get_streams');
		if (data.status === 'ok' && Array.isArray(data.streams)) {
			const select = document.getElementById('streamFilter');
			// clear except first option
			select.querySelectorAll('option:not([value=""])').forEach(o => o.remove());
			data.streams.forEach(s => {
				const opt = document.createElement('option');
				opt.value = s;
				opt.textContent = s;
				select.appendChild(opt);
			});
		}
	} catch (err) {
		console.error('Failed to load streams', err);
	}
}

async function filterByStream() {
	closeEditor();
	const stream = document.getElementById('streamFilter').value;
	const tbody = document.querySelector('#streamTable tbody');
	if (!stream) {
		alert('Please select a stream');
		return;
	}
	tbody.innerHTML = '<tr><td colspan="6">Loading…</td></tr>';
	try {
		const data = await fetchJSON('backend/admin_api.php?action=students_by_stream&stream=' + encodeURIComponent(stream));
		if (data.status === 'ok') {
			tbody.innerHTML = '';
			if (!data.students || data.students.length === 0) {
				tbody.innerHTML = `<tr><td colspan="5" class="empty-state">No students found in ${escapeHtml(stream)}</td></tr>`;
				return;
			}
			data.students.forEach(s => {
				const tr = document.createElement('tr');
				tr.innerHTML = `<td>${escapeHtml(s.id)}</td><td>${escapeHtml(s.name)}</td><td>${escapeHtml(s.email)}</td><td>${escapeHtml(s.contact || s.contactno || 'N/A')}</td><td>${escapeHtml(s.stream)}</td><td><button class="btn-edit" onclick="editStudent('${escapeHtml(s.id)}')">Edit</button></td>`;
				tbody.appendChild(tr);
			});
		} else {
			tbody.innerHTML = '<tr><td colspan="6">Error loading students</td></tr>';
		}
	} catch (err) {
		console.error(err);
		tbody.innerHTML = '<tr><td colspan="5">Error loading students</td></tr>';
	}
}

function resetStreamFilter() {
	closeEditor();
	const select = document.getElementById('streamFilter');
	select.value = '';
	const tbody = document.querySelector('#streamTable tbody');
	tbody.innerHTML = '<tr><td colspan="6" class="empty-state">Select a stream to view students</td></tr>';
}

async function showUser(id) {
	document.getElementById('profileArea').innerHTML = 'Loading profile…';
	document.getElementById('predictionsArea').innerHTML = 'Loading predictions…';
	try {
		const [uData, pData] = await Promise.all([
			fetchJSON('backend/admin_api.php?action=user_details&id=' + encodeURIComponent(id)),
			fetchJSON('backend/admin_api.php?action=user_predictions&id=' + encodeURIComponent(id))
		]);

		if (uData.status === 'ok' && uData.user) {
			const u = uData.user;
			document.getElementById('profileArea').innerHTML = `
				<div><strong>Name:</strong> ${escapeHtml(u.name)}</div>
				<div><strong>Roll No:</strong> ${escapeHtml(u.id)}</div>
				<div><strong>Email:</strong> ${escapeHtml(u.email)}</div>
				<div><strong>Contact:</strong> ${escapeHtml(u.contactno || u.contact || 'N/A')}</div>
				<div><strong>Stream:</strong> ${escapeHtml(u.stream || 'N/A')}</div>
			`;
		} else {
			document.getElementById('profileArea').innerHTML = 'User not found.';
		}

		if (pData.status === 'ok') {
			const preds = pData.predictions;
			if (!preds || preds.length === 0) {
				document.getElementById('predictionsArea').innerHTML = '<div>No predictions found for this user.</div>';
			} else {
				document.getElementById('predictionsArea').innerHTML = preds.map(p => `
					<div class="prediction">
						<div><strong>Result:</strong> ${escapeHtml(p.result)}</div>
						<div><small>${escapeHtml(p.created_at)}</small></div>
						<div style="margin-top:6px"><pre style="white-space:pre-wrap;">${escapeHtml(p.input_data)}</pre></div>
					</div>
				`).join('');
			}
		} else {
			document.getElementById('predictionsArea').innerHTML = 'Could not load predictions.';
		}

	} catch (err) {
		console.error(err);
		document.getElementById('profileArea').innerHTML = 'Error loading profile.';
		document.getElementById('predictionsArea').innerHTML = '';
	}
}

// editing helpers

async function editStudent(id) {
	document.getElementById('editorArea').style.display = 'none';
	try {
		const data = await fetchJSON('backend/admin_api.php?action=user_details&id=' + encodeURIComponent(id));
		if (data.status === 'ok' && data.user) {
			const u = data.user;
			document.getElementById('editId').value = u.id;
			document.getElementById('editName').value = u.name || '';
			document.getElementById('editEmail').value = u.email || '';
			document.getElementById('editContact').value = u.contactno || u.contact || '';
			document.getElementById('editStream').value = u.stream || '';
			document.getElementById('editorArea').style.display = 'block';
		} else {
			alert('User not found');
		}
	} catch (err) {
		console.error(err);
		alert('Failed to load user data');
	}
}

async function saveStudent() {
	const id = document.getElementById('editId').value;
	const name = document.getElementById('editName').value;
	const email = document.getElementById('editEmail').value;
	const contact = document.getElementById('editContact').value;
	const stream = document.getElementById('editStream').value;
	try {
		const res = await fetch('backend/admin_api.php?action=update_user', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ id, name, email, contact, stream })
		});
		const data = await res.json();
		if (data.status === 'ok') {
			alert('Student updated successfully');
			closeEditor();
			// update stream dropdown in case the stream changed
			loadStreams();
			const currentStream = document.getElementById('streamFilter')?.value;
			if (currentStream) filterByStream();
			else loadUsers();
		} else {
			alert('Update failed: ' + data.message);
		}
	} catch (err) {
		console.error(err);
		alert('Error saving student');
	}
}

function closeEditor() {
	document.getElementById('editorArea').style.display = 'none';
}

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
	loadUsers();
	loadStreams();
});

