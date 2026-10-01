let edit = false;

const API = 'api/autoparks-api.php'
const FOTO_PATH = 'uploads/autoparks/'

document.querySelector('#new-btn').addEventListener('click', (e) => {
    e.preventDefault()
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
        await deleteAuto(id)
        await fetchAutoparks()
        document.querySelector('#deleteModal').style.display = 'none'
    }
})
document.querySelector('#foto').addEventListener('change', (e) => {
    const file = e.target.files[0]
    if (file) setPreview(URL.createObjectURL(file))
})

fetchAutoparks()

function esc(v){
    const d = document.createElement('div')
    d.textContent = v ?? ''
    return d.innerHTML
}

function setPreview(src){
    const img = document.querySelector('#fotoPreview')
    if (src) {
        img.src = src
        img.style.display = 'block'
    } else {
        img.removeAttribute('src')
        img.style.display = 'none'
    }
}

function showModal(){
    document.querySelector('#modal-admin').style.display = 'flex'
}

function hideModal(){
    document.querySelector('#modal-admin').style.display = 'none'
    document.querySelector('#pieteikumuForma').reset()
    document.querySelector('#piet_ID').value = ''
    setPreview(null)
    edit = false
}

async function fetchAutoparks(){
    try{
        const response = await fetch(API)
        const auto = await response.json()

        if (!response.ok) throw new Error(auto.error || 'Kļūda')

        let template = ""
        auto.forEach(a => {
            const foto = a.foto
                ? `<img src="${FOTO_PATH}${esc(a.foto)}" alt="" style="width:70px; height:45px; object-fit:cover; border-radius:6px;">`
                : '—'

            template += `
                <tr data-id="${a.auto_id}">
                    <td>${a.auto_id}</td>
                    <td>${foto}</td>
                    <td>${esc(a.numurs)}</td>
                    <td>${esc(a.marka)}</td>
                    <td>${esc(a.modelis)}</td>
                    <td>${a.gads}</td>
                    <td>${esc(a.krasa)}</td>
                    <td>${a.vietu_skaits}</td>
                    <td>${esc(a.statuss)}</td>
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
        await editAuto(id)
    }

    if (deleteBtn) {
        e.preventDefault()
        const id = deleteBtn.closest('tr').getAttribute('data-id')
        await deleteAutoModal(id)
    }
}

async function editAuto(id){
    try{
        const response = await fetch(`${API}?id=${id}`)
        const a = await response.json()

        if (!response.ok) throw new Error(a.error || 'Kļūda')

        document.querySelector('#numurs').value = a.numurs
        document.querySelector('#marka').value = a.marka
        document.querySelector('#modelis').value = a.modelis
        document.querySelector('#gads').value = a.gads
        document.querySelector('#krasa').value = a.krasa
        document.querySelector('#vietu_skaits').value = a.vietu_skaits
        document.querySelector('#piet_ID').value = a.auto_id
        setPreview(a.foto ? FOTO_PATH + a.foto : null)

        edit = true
        showModal()
    }catch(err){
        alert("Neizdevās ielādēt automašīnu! Mēģiniet vēlreiz!")
        console.error(err)
    }
}

async function handleFormSubmit(e){
    e.preventDefault()

    // FormData, jo jānosūta fails. Content-Type nenorāda — pārlūks uzliks pats
    const formData = new FormData()
    formData.append('numurs', document.querySelector('#numurs').value)
    formData.append('marka', document.querySelector('#marka').value)
    formData.append('modelis', document.querySelector('#modelis').value)
    formData.append('gads', document.querySelector('#gads').value)
    formData.append('krasa', document.querySelector('#krasa').value)
    formData.append('vietu_skaits', document.querySelector('#vietu_skaits').value)

    const file = document.querySelector('#foto').files[0]
    if (file) formData.append('foto', file)

    const id = document.querySelector('#piet_ID').value
    const url = edit ? `${API}?id=${id}` : API

    try{
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        })

        if (!response.ok) {
            const data = await response.json()
            alert(data.error || "Neizdevās saglabāt automašīnu!")
            return
        }

        hideModal()
        await fetchAutoparks()
    }catch(err){
        alert("Neizdevās saglabāt automašīnu! Mēģiniet vēlreiz!")
        console.error(err)
    }
}

async function deleteAutoModal(id){
    try{
        const response = await fetch(`${API}?id=${id}`)
        const a = await response.json()

        if (!response.ok) throw new Error(a.error || 'Kļūda')

        document.querySelector('#deleteMessage span').textContent = a.numurs
        document.querySelector('#confirmDelete').setAttribute('data-id', id)
        document.querySelector('#deleteModal').style.display = 'flex'
    }catch(err){
        alert("Neizdevās ielādēt automašīnu!")
        console.error(err)
    }
}

async function deleteAuto(id){
    try{
        const response = await fetch(`${API}?id=${id}`, {method: 'DELETE'})

        if (!response.ok) {
            const data = await response.json()
            alert(data.error || "Neizdevās dzēst automašīnu!")
        }
    }catch(err){
        alert("Neizdevās dzēst automašīnu! Mēģiniet vēlreiz!")
        console.error(err)
    }
}