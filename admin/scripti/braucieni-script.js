let edit = false;

const API = 'api/braucieni-api.php'

document.querySelector('#new-btn').addEventListener('click', async (e) => {
    e.preventDefault()
    await loadLists()
    updatePreview()
    showModal()
})
document.querySelector('.close-modal').addEventListener('click', hideModal)
document.addEventListener('click', handleTableClick)
document.querySelector('#pieteikumuForma').addEventListener('submit', handleFormSubmit)
document.querySelector('#cancelDelete').addEventListener('click', () => {
    document.querySelector('#deleteModal').style.display = 'none'
})
document.querySelector('#confirmDelete').addEventListener('click', async (e) => {
    const id = e.target.getAttribute('data-id')

    if (id) {
        await deleteBrauciens(id)
        await fetchBraucieni()
        document.querySelector('#deleteModal').style.display = 'none'
    }
})
;['#tarifs_id', '#km', '#minutes'].forEach(sel => {
    document.querySelector(sel).addEventListener('input', updatePreview)
})

fetchBraucieni()

function esc(v){
    const d = document.createElement('div')
    d.textContent = v ?? ''
    return d.innerHTML
}

function formatDate(dateString){
    const date = new Date(dateString.replace(' ', 'T'))

    const day = String(date.getDate()).padStart(2, '0')
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const year = date.getFullYear()
    const hours = String(date.getHours()).padStart(2, '0')
    const minutes = String(date.getMinutes()).padStart(2, '0')

    return `${day}.${month}.${year} ${hours}:${minutes}`
}

function showModal(){
    document.querySelector('#modal-admin').style.display = 'flex'
}

function hideModal(){
    document.querySelector('#modal-admin').style.display = 'none'
    document.querySelector('#pieteikumuForma').reset()
    document.querySelector('#piet_ID').value = ''
    document.querySelector('#cenaPreview').textContent = ''
    edit = false
}

// Aprēķinātā cena formā (informatīva; galīgo cenu rēķina serveris)
function updatePreview(){
    const opt = document.querySelector('#tarifs_id').selectedOptions[0]
    const km = parseFloat(document.querySelector('#km').value)
    const min = parseInt(document.querySelector('#minutes').value)
    const preview = document.querySelector('#cenaPreview')

    if (!opt || isNaN(km) || isNaN(min)) {
        preview.textContent = ''
        return
    }

    const cena = parseFloat(opt.dataset.iekr) + km * parseFloat(opt.dataset.km) + min * parseFloat(opt.dataset.min)
    preview.textContent = `Aprēķinātā cena: ${cena.toFixed(2)} €`
}

// Aizpilda klientu, tarifu un šoferu izvēlnes
async function loadLists(){
    try{
        const response = await fetch(`${API}?saraksti=1`)
        const d = await response.json()

        if (!response.ok) throw new Error(d.error || 'Kļūda')

        document.querySelector('#klients_id').innerHTML = d.klienti
            .map(k => `<option value="${k.klients_id}">${esc(k.vards)} (${esc(k.talrunis)})</option>`)
            .join('')

        document.querySelector('#tarifs_id').innerHTML = d.tarifi
            .map(t => `<option value="${t.tarifs_id}" data-iekr="${t.iekrausanas_maksa}" data-km="${t.cena_km}" data-min="${t.cena_min}">${esc(t.nosaukums)} (${esc(t.kods)})</option>`)
            .join('')

        document.querySelector('#sofers_id').innerHTML = '<option value="">— nav piešķirts —</option>' + d.soferi
            .map(s => `<option value="${s.sofers_id}">${esc(s.vards)} ${esc(s.uzvards)}</option>`)
            .join('')
    }catch(err){
        alert("Neizdevās ielādēt sarakstus!")
        console.error(err)
    }
}

async function fetchBraucieni(){
    try{
        const response = await fetch(API)
        const braucieni = await response.json()

        if (!response.ok) throw new Error(braucieni.error || 'Kļūda')

        let template = ""
        braucieni.forEach(b => {
            const sofers = b.sofers_vards
                ? `${esc(b.sofers_vards)} ${esc(b.sofers_uzvards)}${b.auto_numurs ? ' (' + esc(b.auto_numurs) + ')' : ''}`
                : '—'
            const cena = b.cena !== null ? `${b.cena} €` : '—'

            template += `
                <tr data-id="${b.brauciens_id}">
                    <td>${b.brauciens_id}</td>
                    <td>${formatDate(b.izveidots)}</td>
                    <td>${esc(b.klients_vards)}</td>
                    <td>${esc(b.no_adrese)} → ${esc(b.uz_adrese)}</td>
                    <td>${sofers}</td>
                    <td>${esc(b.tarifs_nosaukums)}</td>
                    <td>${b.km ?? '—'}</td>
                    <td>${b.minutes ?? '—'}</td>
                    <td>${cena}</td>
                    <td>${esc(b.statuss)}</td>
                    <td>
                        <a class="pieteikums-item btn-edit"><i class="fa fa-edit"></i></a>
                        <a class="pieteikums-delete btn-delete"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
            `
        })

        document.querySelector('#pieteikumi').innerHTML = template
    }catch(err){
        alert("Neizdevās ielādēt datus! Mēģiniet vēlreiz!")
        console.error(err)
    }
}

async function handleTableClick(e){
    const editBtn = e.target.closest('.pieteikums-item')
    const deleteBtn = e.target.closest('.pieteikums-delete')

    if (editBtn) {
        e.preventDefault()
        const id = editBtn.closest('tr').getAttribute('data-id')
        await editBrauciens(id)
    }

    if (deleteBtn) {
        e.preventDefault()
        const id = deleteBtn.closest('tr').getAttribute('data-id')
        await deleteBrauciensModal(id)
    }
}

async function editBrauciens(id){
    try{
        await loadLists()

        const response = await fetch(`${API}?id=${id}`)
        const b = await response.json()

        if (!response.ok) throw new Error(b.error || 'Kļūda')

        document.querySelector('#klients_id').value = b.klients_id
        document.querySelector('#tarifs_id').value = b.tarifs_id
        document.querySelector('#sofers_id').value = b.sofers_id ?? ''
        document.querySelector('#no_adrese').value = b.no_adrese
        document.querySelector('#uz_adrese').value = b.uz_adrese
        document.querySelector('#km').value = b.km ?? ''
        document.querySelector('#minutes').value = b.minutes ?? ''
        document.querySelector('#statuss').value = b.statuss
        document.querySelector('#piet_ID').value = b.brauciens_id

        updatePreview()
        edit = true
        showModal()
    }catch(err){
        alert("Neizdevās ielādēt braucienu! Mēģiniet vēlreiz!")
        console.error(err)
    }
}

async function handleFormSubmit(e){
    e.preventDefault()

    const formData = {
        klients_id: document.querySelector('#klients_id').value,
        tarifs_id: document.querySelector('#tarifs_id').value,
        sofers_id: document.querySelector('#sofers_id').value,
        no_adrese: document.querySelector('#no_adrese').value,
        uz_adrese: document.querySelector('#uz_adrese').value,
        km: document.querySelector('#km').value,
        minutes: document.querySelector('#minutes').value,
        statuss: document.querySelector('#statuss').value
    }

    const id = document.querySelector('#piet_ID').value
    const method = edit ? 'PUT' : 'POST'
    const url = edit ? `${API}?id=${id}` : API

    try{
        const response = await fetch(url, {
            method: method,
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        })

        if (!response.ok) {
            const data = await response.json()
            alert(data.error || "Neizdevās saglabāt braucienu!")
            return
        }

        hideModal()
        await fetchBraucieni()
    }catch(err){
        alert("Neizdevās saglabāt braucienu! Mēģiniet vēlreiz!")
        console.error(err)
    }
}

async function deleteBrauciensModal(id){
    try{
        const response = await fetch(`${API}?id=${id}`)
        const b = await response.json()

        if (!response.ok) throw new Error(b.error || 'Kļūda')

        document.querySelector('#deleteMessage span').textContent = `#${b.brauciens_id} (${b.no_adrese} → ${b.uz_adrese})`
        document.querySelector('#confirmDelete').setAttribute('data-id', id)
        document.querySelector('#deleteModal').style.display = 'flex'
    }catch(err){
        alert("Neizdevās ielādēt braucienu!")
        console.error(err)
    }
}

async function deleteBrauciens(id){
    try{
        const response = await fetch(`${API}?id=${id}`, {method: 'DELETE'})

        if (!response.ok) {
            const data = await response.json()
            alert(data.error || "Neizdevās dzēst braucienu!")
        }
    }catch(err){
        alert("Neizdevās dzēst braucienu! Mēģiniet vēlreiz!")
        console.error(err)
    }
}