(function(){
  const root=document.documentElement, base=root.dataset.base||'/mini-game/', role=root.dataset.role||'screen';
  let csrf=root.dataset.csrf||'', snapshot=null, busy=false;
  const $=s=>document.querySelector(s), esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  function toast(msg){let e=$('.toast');if(!e){e=document.createElement('div');e.className='toast';document.body.append(e)}e.textContent=msg;e.hidden=false;setTimeout(()=>e.hidden=true,2600)}
  async function post(path,data){const body=new URLSearchParams({...data,csrf_test_name:csrf});const r=await fetch(base+path,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body});const j=await r.json();if(j.csrf)csrf=j.csrf;if(!r.ok)throw new Error(j.message||'Có lỗi xảy ra');return j}
  function remaining(s){if(!s.countdown_ends_at)return 0;const stamp=new Date(String(s.countdown_ends_at).replace(' ','T')).getTime();return Math.max(0,Math.ceil((stamp-Date.now())/1000))}
  function draw(data){snapshot=data;const s=data.state||{};document.querySelectorAll('[data-question]').forEach(e=>e.textContent=s.question_display||'--');document.querySelectorAll('[data-prompt]').forEach(e=>e.textContent=s.question_prompt||'Chờ MC bắt đầu game');document.querySelectorAll('[data-admin-answer]').forEach(e=>e.textContent=s.question_answer||'—');document.querySelectorAll('[data-timer]').forEach(e=>e.textContent=remaining(s));document.querySelectorAll('[data-status]').forEach(e=>e.textContent=s.status==='playing'?'ĐANG CHƠI':s.status==='finished'?'KẾT THÚC':'CHỜ BẮT ĐẦU');
    const current=(data.buzzes||[]).find(b=>b.status==='answering');document.querySelectorAll('[data-answering]').forEach(e=>e.textContent=current?current.name+' · '+current.office:'Chưa có người giành quyền');
    document.querySelectorAll('[data-answer]').forEach(e=>{e.classList.toggle('hidden',!Number(s.answer_revealed));e.textContent=s.question_answer||''});
    const list=$('[data-buzzes]');if(list)list.innerHTML=(data.buzzes||[]).map((b,i)=>`<div class="buzz-item ${esc(b.status)}"><span class="rank">#${i+1}</span><div><b>${esc(b.name)}</b><div class="muted">${esc(b.office)}</div></div><span class="status">${b.status==='answering'?'ĐANG TRẢ LỜI':b.status==='wrong'?'ĐÃ SAI':b.status==='correct'?'ĐÚNG':'CHỜ'}</span></div>`).join('')||'<p class="muted">Chưa có lượt bấm.</p>';
    const scores=$('[data-scores]');if(scores)scores.innerHTML=(data.scores||[]).map((x,i)=>`<div class="score"><span>${i+1}. ${esc(x.office)}</span><b>${x.score} điểm</b></div>`).join('')||'<p class="muted">Chưa có điểm.</p>';
    if(role==='player'){const btn=$('[data-buzzer]'),msg=$('[data-player-message]'),token=localStorage.getItem('miniGameToken'),me=data.me,buzz=(data.buzzes||[]).find(x=>me&&Number(x.player_id)===Number(me.id));if($('#join-view')){$('#join-view').classList.toggle('hidden',!!me);$('#play-view').classList.toggle('hidden',!me)}if(btn){btn.disabled=!me||!Number(s.buzz_open)||!!buzz||s.status!=='playing';msg.textContent=buzz?(buzz.status==='answering'?'Bạn đang được quyền trả lời!':buzz.status==='wrong'?'Bạn đã mất lượt ở câu này.':'Bạn đã giành quyền — hãy chờ MC!'):Number(s.buzz_open)?'Bấm thật nhanh khi bạn biết đáp án!':'MC chưa mở lượt giành quyền.'}}
    if(role==='admin'){
      const labels={plate_to_province:'Biển số → Tỉnh/thành',province_to_plate:'Tỉnh/thành → Biển số',places:'Địa điểm nổi tiếng',specialty:'Đặc sản',airport:'Sân bay',unesco:'Di sản UNESCO'};
      document.querySelectorAll('[data-round]').forEach(e=>e.textContent=Number(s.question_number)>0?'Câu '+s.question_number:'—');
      document.querySelectorAll('[data-buzz-count]').forEach(e=>e.textContent=(data.buzzes||[]).length);
      document.querySelectorAll('[data-question-type]').forEach(e=>e.textContent=labels[s.question_type]||'Chưa chọn dạng câu');
      const help=$('[data-judge-help]');if(help)help.textContent=current?'Đang chờ '+current.name+' trả lời qua Microsoft Teams.':s.status==='playing'?'Chờ người chơi bấm “GIÀNH QUYỀN”.':'Bấm “Bắt đầu game” khi mọi người đã sẵn sàng.';
      document.querySelectorAll('[data-admin-action="judge"]').forEach(e=>e.disabled=!current);
      document.querySelectorAll('[data-admin-action="start"]').forEach(e=>e.disabled=s.status!=='waiting');
      document.querySelectorAll('[data-admin-action="next"]').forEach(e=>e.disabled=s.status!=='playing');
      document.querySelectorAll('[data-admin-action="reveal"]').forEach(e=>e.disabled=!s.question_id||Number(s.answer_revealed)===1);
      document.querySelectorAll('[data-admin-action="reset-buzz"]').forEach(e=>e.disabled=!s.question_id);
    }
  }
  async function poll(){try{const token=role==='player'?localStorage.getItem('miniGameToken')||'':'';const r=await fetch(base+'state?token='+encodeURIComponent(token),{cache:'no-store'});if(r.ok)draw(await r.json())}catch(e){}finally{setTimeout(poll,1000)}}
  document.addEventListener('click',async e=>{const cmd=e.target.closest('[data-command]');if(cmd&&!busy){busy=true;try{await post('command',{action:cmd.dataset.command,seconds:cmd.dataset.seconds||''});await refresh()}catch(x){toast(x.message)}finally{busy=false}}const buzz=e.target.closest('[data-buzzer]');if(buzz&&!busy){busy=true;try{await post('buzz',{token:localStorage.getItem('miniGameToken')||''});buzz.disabled=true;await refresh()}catch(x){toast(x.message)}finally{busy=false}}});
  const join=$('[data-join]');if(join)join.addEventListener('submit',async e=>{e.preventDefault();try{const fd=new FormData(join),j=await post('join',{name:fd.get('name'),office:fd.get('office')});localStorage.setItem('miniGameToken',j.token);await refresh()}catch(x){toast(x.message)}});
  async function refresh(){const token=role==='player'?localStorage.getItem('miniGameToken')||'':'';const r=await fetch(base+'state?token='+encodeURIComponent(token),{cache:'no-store'});if(r.ok)draw(await r.json())}
  setInterval(()=>{if(snapshot)document.querySelectorAll('[data-timer]').forEach(e=>e.textContent=remaining(snapshot.state||{}))},250);poll();
})();
