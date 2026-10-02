let edit = false;

const API = 'api/tarifi-api.php'

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
        await deleteTarifs(id)
        await fetchTarifi()
        document.querySelector('#deleteModal').style.display = 'none'
    }
})

fetchTarifi()

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

// Aizpilda automašīnu izvēlni no autoparka
async function loadAutos(){
    try{
        const response = await fetch(`${API}?auto_saraksts=1`)
        const autos = await response.json()

        if (!response.ok) throw new Error(autos.error || 'Kļūda')

        let options = '<option value="">— izvēlies automašīnu —</option>'
        autos.forEach(a => {
            options += `<option value="${a.auto_id}">${esc(a.marka)} ${esc(a.modelis)} (${a.vietu_skaits} vietas)</option>`
        })
        document.querySelector('#auto_id').innerHTML = options
    }catch(err){
        alert("Neizdevās ielādēt automašīnu sarakstu!")
        console.error(err)
    }
}

async function fetchTarifi(){
    try{
        const response = await fetch(API)
        const tarifi = await response.json()

        if (!response.ok) throw new Error(tarifi.error || 'Kļūda')

        let template = ""
        tarifi.forEach(t => {
            const auto = t.marka
                ? `${esc(t.marka)} ${esc(t.modelis)}`
                : (esc(t.automasina) || '—')

            template += `
                <tr data-id="${t.tarifs_id}">
                    <td>${t.tarifs_id}</td>
                    <td>${esc(t.kods)}</td>
                    <td>${esc(t.nosaukums)}</td>
                    <td>${auto}</td>
                    <td>${t.vietu_skaits}</td>
                    <td>${t.cena_km}</td>
                    <td>${t.cena_min}</td>
                    <td>${esc(t.birka)}</td>
                    <td>${esc(t.statuss)}</td>
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
        await editTarifs(id)
    }

    if (deleteBtn) {
        e.preventDefault()
        const id = deleteBtn.closest('tr').getAttribute('data-id')
        await deleteTarifsModal(id)
    }
}

async function editTarifs(id){
    try{
        await loadAutos()

        const response = await fetch(`${API}?id=${id}`)
        const t = await response.json()

        if (!response.ok) throw new Error(t.error || 'Kļūda')

        document.querySelector('#kods').value = t.kods
        document.querySelector('#nosaukums').value = t.nosaukums
        document.querySelector('#auto_id').value = t.auto_id ?? ''
        document.querySelector('#iekrausanas_maksa').value = t.iekrausanas_maksa
        document.querySelector('#cena_km').value = t.cena_km
        document.querySelector('#cena_min').value = t.cena_min
        document.querySelector('#birka').value = t.birka
        document.querySelector('#piet_ID').value = t.tarifs_id

        edit = true
        showModal()
    }catch(err){
        alert("Neizdevās ielādēt tarifu! Mēģiniet vēlreiz!")
        console.error(err)
    }
}

async function handleFormSubmit(e){
    e.preventDefault()

    const formData = {
        kods: document.querySelector('#kods').value,
        nosaukums: document.querySelector('#nosaukums').value,
        auto_id: document.querySelector('#auto_id').value,
        iekrausanas_maksa: document.querySelector('#iekrausanas_maksa').value,
        cena_km: document.querySelector('#cena_km').value,
        cena_min: document.querySelector('#cena_min').value,
        birka: document.querySelector('#birka').value
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

        // Kļūdas gadījumā paziņojumu parāda pazinojumi.js, forma paliek atvērta
        if (!response.ok) return

        hideModal()
        await fetchTarifi()
    }catch(err){
        alert("Neizdevās saglabāt tarifu! Mēģiniet vēlreiz!")
        console.error(err)
    }
}

async function deleteTarifsModal(id){
    try{
        const response = await fetch(`${API}?id=${id}`)
        const t = await response.json()

        if (!response.ok) throw new Error(t.error || 'Kļūda')

        document.querySelector('#deleteMessage span').textContent = t.nosaukums
        document.querySelector('#confirmDelete').setAttribute('data-id', id)
        document.querySelector('#deleteModal').style.display = 'flex'
    }catch(err){
        alert("Neizdevās ielādēt tarifu!")
        console.error(err)
    }
}

async function deleteTarifs(id){
    try{
        await fetch(`${API}?id=${id}`, {method: 'DELETE'})
    }catch(err){
        alert("Neizdevās dzēst tarifu! Mēģiniet vēlreiz!")
        console.error(err)
    }
}