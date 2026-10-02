const API = 'api/klienti-api.php'

document.querySelector('.close-modal').addEventListener('click', hideModal)
document.addEventListener('click', handleTableClick)
document.querySelector('#pieteikumuForma').addEventListener('submit', handleFormSubmit)
document.querySelector('#cancelDelete').addEventListener('click', () => {
    document.querySelector('#deleteModal').style.display = 'none'
})
document.querySelector('#confirmDelete').addEventListener('click', async (e) => {
    const id = e.target.getAttribute('data-id')

    if (id) {
        await deleteKlients(id)
        await fetchKlienti()
        document.querySelector('#deleteModal').style.display = 'none'
    }
})

fetchKlienti()

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
}

async function fetchKlienti(){
    try{
        const response = await fetch(API)
        const klienti = await response.json()

        if (!response.ok) throw new Error(klienti.error || 'Kļūda')

        let template = ""
        klienti.forEach(k => {
            template += `
                <tr data-id="${k.klients_id}">
                    <td>${k.klients_id}</td>
                    <td>${esc(k.vards)}</td>
                    <td>${esc(k.talrunis)}</td>
                    <td>${esc(k.epasts) || '—'}</td>
                    <td>${formatDate(k.izveidots)}</td>
                    <td>${esc(k.statuss)}</td>
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
        await editKlients(id)
    }

    if (deleteBtn) {
        e.preventDefault()
        const id = deleteBtn.closest('tr').getAttribute('data-id')
        await deleteKlientsModal(id)
    }
}

async function editKlients(id){
    try{
        const response = await fetch(`${API}?id=${id}`)
        const k = await response.json()

        if (!response.ok) throw new Error(k.error || 'Kļūda')

        document.querySelector('#vards').value = k.vards
        document.querySelector('#talrunis').value = k.talrunis
        document.querySelector('#epasts').value = k.epasts ?? ''
        document.querySelector('#piet_ID').value = k.klients_id

        showModal()
    }catch(err){
        alert("Neizdevās ielādēt klientu! Mēģiniet vēlreiz!")
        console.error(err)
    }
}

async function handleFormSubmit(e){
    e.preventDefault()

    const formData = {
        vards: document.querySelector('#vards').value,
        talrunis: document.querySelector('#talrunis').value,
        epasts: document.querySelector('#epasts').value
    }

    const id = document.querySelector('#piet_ID').value

    try{
        const response = await fetch(`${API}?id=${id}`, {
            method: 'PUT',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        })

        if (!response.ok) {
            const data = await response.json()
            alert(data.error || "Neizdevās saglabāt klientu!")
            return
        }

        hideModal()
        await fetchKlienti()
    }catch(err){
        alert("Neizdevās saglabāt klientu! Mēģiniet vēlreiz!")
        console.error(err)
    }
}

async function deleteKlientsModal(id){
    try{
        const response = await fetch(`${API}?id=${id}`)
        const k = await response.json()

        if (!response.ok) throw new Error(k.error || 'Kļūda')

        document.querySelector('#deleteMessage span').textContent = k.vards
        document.querySelector('#confirmDelete').setAttribute('data-id', id)
        document.querySelector('#deleteModal').style.display = 'flex'
    }catch(err){
        alert("Neizdevās ielādēt klientu!")
        console.error(err)
    }
}

async function deleteKlients(id){
    try{
        const response = await fetch(`${API}?id=${id}`, {method: 'DELETE'})

        if (!response.ok) {
            const data = await response.json()
            alert(data.error || "Neizdevās dzēst klientu!")
        }
    }catch(err){
        alert("Neizdevās dzēst klientu! Mēģiniet vēlreiz!")
        console.error(err)
    }
}