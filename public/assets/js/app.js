(() => {
  const page = document.body.dataset.page;
  let session;
  const feedback = (message, error = false) => {
    const box = document.querySelector('#feedback');
    box.className = `flash flash--${error ? 'error' : 'success'}`;
    box.textContent = message;
    box.hidden = false;
    box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  };
  async function request(path, data) {
    const response = await fetch(`/api/${path}`, {
      ...(data ? { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': session.csrf }, body: JSON.stringify(data) } : {}),
    });
    const result = await response.json();
    if (!response.ok) throw new Error(result.error || 'Não foi possível concluir a operação.');
    return result;
  }
  const resetCaregiverForm = () => {
    const form = document.querySelector('[data-api="caregiver"]');
    form.reset();
    form.elements.id.value = '';
    form.elements.password.required = true;
    form.elements.password_confirm.required = true;
    document.querySelector('#caregiver-heading').textContent = 'Cadastrar cuidador';
    document.querySelector('#cancel-edit').hidden = true;
  };
  async function loadCaregivers() {
    const { caregivers } = await request('caregivers');
    const body = document.querySelector('#caregivers');
    body.replaceChildren();
    document.querySelector('#caregiver-empty').hidden = caregivers.length > 0;
    document.querySelector('#caregiver-empty').textContent = 'Nenhum cuidador cadastrado ainda.';
    document.querySelector('#caregiver-table').hidden = !caregivers.length;
    for (const caregiver of caregivers) {
      const row = document.createElement('tr');
      for (const field of ['name','cpf','email','phone','specialties','status']) {
        const cell = document.createElement('td');
        cell.textContent = field === 'status' ? (caregiver.status === 'active' ? 'Ativo' : 'Inativo') : caregiver[field] || '';
        row.append(cell);
      }
      const actions = document.createElement('td');
      actions.className = 'table-actions';
      const button = (label, handler) => {
        const element = document.createElement('button');
        element.type = 'button';
        element.className = 'btn btn-secondary btn-sm';
        element.textContent = label;
        element.addEventListener('click', async () => {
          element.disabled = true;
          try { await handler(); } catch (error) { feedback(error.message, true); } finally { element.disabled = false; }
        });
        actions.append(element);
      };
      button('Editar', () => {
        const form = document.querySelector('[data-api="caregiver"]');
        for (const field of ['id','name','cpf','email','phone','specialties']) form.elements[field].value = caregiver[field] || '';
        form.elements.password.value = '';
        form.elements.password_confirm.value = '';
        form.elements.password.required = false;
        form.elements.password_confirm.required = false;
        document.querySelector('#caregiver-heading').textContent = 'Editar cuidador';
        document.querySelector('#cancel-edit').hidden = false;
        form.elements.name.focus();
      });
      button(caregiver.status === 'active' ? 'Desativar' : 'Ativar', async () => {
        const result = await request(`caregivers/${caregiver.id}/toggle`, {});
        feedback(result.message);
        await loadCaregivers();
      });
      button('Remover vínculo', async () => {
        if (!confirm('Remover o vínculo deste cuidador com a clínica?')) return;
        const result = await request(`caregivers/${caregiver.id}/unlink`, {});
        feedback(result.message);
        resetCaregiverForm();
        await loadCaregivers();
      });
      row.append(actions);
      body.append(row);
    }
  }
  document.querySelector('[data-year]').textContent = new Date().getFullYear();
  const forms = [...document.querySelectorAll('form[data-api]')];
  // Prevent submissions until a CSRF-protected session has been established.
  forms.forEach(form => form.querySelector('[type="submit"]').disabled = true);
  const ready = (async () => {
    session = await request('session');
    document.querySelectorAll('[data-auth]').forEach(element => element.hidden = !session.user);
    document.querySelectorAll('[data-guest]').forEach(element => element.hidden = Boolean(session.user));
    document.querySelectorAll('[data-company]').forEach(element => element.hidden = session.user?.role !== 'company');
    document.querySelectorAll('[data-user-name]').forEach(element => element.textContent = session.user?.name?.split(' ')[0] || '');
    document.querySelectorAll('[data-role-label]').forEach(element => element.textContent = session.user?.role === 'company' ? 'Área da clínica' : 'Área do usuário');
    document.querySelectorAll('.nav-menu a').forEach(link => link.classList.toggle('active', link.pathname === location.pathname));
    if (['dashboard','clinic_caregivers','nossoApp'].includes(page) && !session.user) { location.replace('/login.html'); return; }
    if (page === 'clinic_caregivers' && session.user.role !== 'company') { location.replace('/pages/dashboard.html'); return; }
    if (['login','register','register_company','forgot_password','verify','reset_password'].includes(page) && session.user) { location.replace('/pages/dashboard.html'); return; }
    if (['verify','reset_password'].includes(page)) {
      if (!session.reset || (page === 'reset_password' && !session.reset.verified)) { location.replace('/forgot_password.html'); return; }
      document.querySelectorAll('[data-reset-email]').forEach(element => element.textContent = session.reset.email);
      const preview = document.querySelector('[data-token-preview]');
      if (preview && session.reset.preview) { preview.textContent = `Modo local/teste — seu código: ${session.reset.preview}`; preview.hidden = false; }
    }
    if (page === 'index' && session.user) {
      const links = document.querySelectorAll('.hero-actions a');
      links[0].href = '/pages/dashboard.html'; links[0].textContent = 'Abrir painel';
      links[1].href = '/pages/nossoApp.html'; links[1].textContent = 'Conhecer o app';
    }
    if (page === 'logout') { await request('logout', {}); location.replace('/login.html'); return; }
    if (page === 'clinic_caregivers') { resetCaregiverForm(); await loadCaregivers(); }
    const stored = sessionStorage.getItem('vivabem.message');
    if (stored) { sessionStorage.removeItem('vivabem.message'); feedback(stored); }
    if (new URLSearchParams(location.search).has('error')) feedback('Não foi possível entrar com o Google. Confira a configuração do provedor e tente novamente.', true);
    forms.forEach(form => form.querySelector('[type="submit"]').disabled = false);
  })();
  ready.catch(error => feedback(error.message, true));
  document.querySelector('#cancel-edit')?.addEventListener('click', resetCaregiverForm);
  forms.forEach(form => form.addEventListener('submit', async event => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const button = form.querySelector('[type="submit"]');
    button.disabled = true;
    try {
      await ready;
      const result = await request(form.dataset.api, Object.fromEntries(new FormData(form)));
      if (result.redirect) {
        if (result.message) sessionStorage.setItem('vivabem.message', result.message);
        location.assign(result.redirect);
      } else {
        feedback(result.message);
        if (form.dataset.api === 'caregiver') { resetCaregiverForm(); await loadCaregivers(); }
      }
    } catch (error) { feedback(error.message, true); } finally { button.disabled = false; }
  }));
})();
