const csrf = document.querySelector('meta[name=csrf]')?.content || '';

async function post(url, data) {
  const r = await fetch(url, {
    method: 'POST',
    headers: {'Content-Type': 'application/json', 'X-CSRF-Token': csrf},
    body: JSON.stringify(data)
  });
  return r.json();
}

/* ── Votes (étoiles) ── */
document.addEventListener('click', async (ev) => {
  const star = ev.target.closest('.star');
  if (star && !star.disabled) {
    const box = star.closest('.rating');
    const res = await post('vote.php', {id_submission: +box.dataset.id, note: +star.dataset.note});
    if (!res.success) return alert(res.message);
    box.querySelectorAll('.star').forEach(s => s.classList.toggle('on', +s.dataset.note <= res.note));
    box.querySelector('.avg').textContent = res.note_moyenne;
    box.querySelector('.nbv').textContent = res.nb_votes;
  }

  /* ── Likes de commentaires ── */
  const like = ev.target.closest('.like-btn');
  if (like) {
    const res = await post('comment.php', {action: 'like', id_comment: +like.closest('.comment').dataset.id});
    if (!res.success) return alert(res.message);
    like.classList.toggle('liked', res.liked);
    like.querySelector('span').textContent = res.likes;
  }

  /* ── Suppression de commentaires ── */
  const del = ev.target.closest('.del-btn');
  if (del && confirm('Supprimer ce commentaire ?')) {
    const node = del.closest('.comment');
    const res = await post('comment.php', {action: 'delete', id_comment: +node.dataset.id});
    if (res.success) node.remove(); else alert(res.message);
  }
});

/* ── Ajout de commentaire (le texte est inséré avec textContent : pas de XSS) ── */
function renderComment(c) {
  const d = document.createElement('div');
  d.className = 'comment'; d.dataset.id = c.id_comment;
  d.innerHTML = '<div class="avatar"></div><div class="cb"><strong></strong> <span class="muted"></span><div class="txt"></div>' +
    '<div class="acts"><button class="like-btn" type="button">❤️ <span>0</span></button>' +
    '<button class="del-btn" type="button">🗑 Supprimer</button></div></div>';
  d.querySelector('.avatar').textContent = c.initials;
  d.querySelector('strong').textContent = c.author;
  d.querySelector('.muted').textContent = c.date;
  d.querySelector('.txt').textContent = c.content;
  return d;
}

document.addEventListener('submit', async (ev) => {
  const form = ev.target.closest('.cform');
  if (!form) return;
  ev.preventDefault();
  const input = form.querySelector('input');
  const text = input.value.trim();
  if (!text) return;
  const res = await post('comment.php', {action: 'add', id_submission: +form.dataset.id, content: text});
  if (!res.success) return alert(res.message);
  form.closest('.comments').querySelector('.clist').appendChild(renderComment(res.comment));
  input.value = '';
});
