let edit = false;

const API = 'api/soferi-api.php'

document.querySelector('#new-btn').addEventListener('click', async (e) => {
    e.preventDefault()
    await loadAutos()
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
        await deleteSofers(id)
        await fetchSoferi()
        document.querySelector('#deleteModal').style.display = 'none'
    }
})

fetchSoferi()

function esc(v){
    const d = document.createElement('div')
    d.textContent = v ?? ''
    return d.innerHTML
}

function showModal(){
    document.querySelector('#modal-admin').style.display = 'flex'
}

function hideModal(){
    document.querySelector('#modal-admin').style.display = 'none'
    document.querySelector('#pieteikumuForma').reset()
    document.querySelector('#piet_ID').value = ''
    edit = false
}

// Aizpilda automašīnu izvēlni
async function loadAutos(){
    try{
        const response = await fetch(`${API}?auto_saraksts=1`)
        const autos = await response.json()

        if (!response.ok) throw new Error(autos.error || 'Kļūda')

        let options = '<option value="">— nav piešķirta —</option>'
        autos.forEach(a => {
            options += `<option value="${a.auto_id}">${esc(a.numurs)} — ${esc(a.marka)} ${esc(a.modelis)}</option>`
        })
        document.querySelector('#auto_id').innerHTML = options
    }catch(err){
        alert("Neizdevās ielādēt automašīnu sarakstu!")
        console.error(err)
    }
}

async function fetchSoferi(){
    try{
        const response = await fetch(API)
        const soferi = await response.json()

        if (!response.ok) throw new Error(soferi.error || 'Kļūda')

        let template = ""
        soferi.forEach(s => {
            const auto = s.auto_numurs
                ? `${esc(s.auto_numurs)} (${esc(s.marka)} ${esc(s.modelis)})`
                : '—'

            template += `
                <tr data-id="${s.sofers_id}">
                    <td>${s.sofers_id}</td>
                    <td>${esc(s.vards)}</td>
                    <td>${esc(s.uzvards)}</td>
                    <td>${esc(s.talrunis)}</td>
                    <td>${esc(s.licences_nr)}</td>
                    <td>${auto}</td>
                    <td>${esc(s.statuss)}</td>
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
        await editSofers(id)
    }

    if (deleteBtn) {
        e.preventDefault()
        const id = deleteBtn.closest('tr').getAttribute('data-id')
        await deleteSofersModal(id)
    }
}

async function editSofers(id){
    try{
        await loadAutos()

        const response = await fetch(`${API}?id=${id}`)
        const s = await response.json()

        if (!response.ok) throw new Error(s.error || 'Kļūda')

        document.querySelector('#vards').value = s.vards
        document.querySelector('#uzvards').value = s.uzvards
        document.querySelector('#talrunis').value = s.talrunis
        document.querySelector('#licences_nr').value = s.licences_nr
        document.querySelector('#auto_id').value = s.auto_id ?? ''
        document.querySelector('#piet_ID').value = s.sofers_id

        edit = true
        showModal()
    }catch(err){
        alert("Neizdevās ielādēt šoferi! Mēģiniet vēlreiz!")
        console.error(err)
    }
}

async function handleFormSubmit(e){
    e.preventDefault()

    const formData = {
        vards: document.querySelector('#vards').value,
        uzvards: document.querySelector('#uzvards').value,
        talrunis: document.querySelector('#talrunis').value,
        licences_nr: document.querySelector('#licences_nr').value,
        auto_id: document.querySelector('#auto_id').value
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
            alert(data.error || "Neizdevās saglabāt šoferi!")
            return
        }

        hideModal()
        await fetchSoferi()
    }catch(err){
        alert("Neizdevās saglabāt šoferi! Mēģiniet vēlreiz!")
        console.error(err)
    }
}

async function deleteSofersModal(id){
    try{
        const response = await fetch(`${API}?id=${id}`)
        const s = await response.json()

        if (!response.ok) throw new Error(s.error || 'Kļūda')

        document.querySelector('#deleteMessage span').textContent = `${s.vards} ${s.uzvards}`
        document.querySelector('#confirmDelete').setAttribute('data-id', id)
        document.querySelector('#deleteModal').style.display = 'flex'
    }catch(err){
        alert("Neizdevās ielādēt šoferi!")
        console.error(err)
    }
}

async function deleteSofers(id){
    try{
        const response = await fetch(`${API}?id=${id}`, {method: 'DELETE'})

        if (!response.ok) {
            const data = await response.json()
            alert(data.error || "Neizdevās dzēst šoferi!")
        }
    }catch(err){
        alert("Neizdevās dzēst šoferi! Mēģiniet vēlreiz!")
        console.error(err)
    }
}