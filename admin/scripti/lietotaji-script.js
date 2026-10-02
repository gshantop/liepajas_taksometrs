let edit = false;

document.querySelector('#new-btn').addEventListener('click', (e) => {
    e.preventDefault()
    showModal()
})
document.querySelector('.close-modal').addEventListener('click', hideModal)
document.addEventListener('click', handleTableClick)
document.querySelector("#pieteikumuForma").addEventListener('submit', handleFormSubmit)
document.querySelector('#showPassword').addEventListener('click', (e) => {
    e.preventDefault()

    const passwordBlock = document.querySelector('.password')
    const passwordInput = document.querySelector('#parole')

    const isHidden = passwordBlock.style.display === 'none' || passwordBlock.style.display === ''

    if (isHidden) {
        passwordBlock.style.display = 'block'
    } else {
        passwordBlock.style.display = 'none'
        passwordInput.value = ''
    }
})
document.querySelector('#cancelDelete').addEventListener('click', () => {
    document.querySelector('#deleteModal').style.display = 'none'
})
document.querySelector('#confirmDelete').addEventListener('click', async (e) => {
    const id = e.target.getAttribute('data-id')

    if(id){
        await deletePieteikums(id)
        await fetchPieteikumi()

        document.querySelector('#deleteModal').style.display = 'none'
    }
})

fetchPieteikumi()

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
    document.querySelector("#pieteikumuForma").reset()
    document.querySelector('#piet_ID').value = ''
    edit = false

    const passwordBlock = document.querySelector('.password')
    const passwordInput = document.querySelector('#parole')

    passwordBlock.style.display = 'none'
    passwordInput.value = ''
}

async function fetchPieteikumi(){
    try{
        const response = await fetch('api/lietotaji-api.php')
        const pieteikumi = await response.json()

        if (!response.ok) throw new Error(pieteikumi.error || 'Kļūda')

        let template = ""
        pieteikumi.forEach( p => {

            if (p.statuss === "dzēsts") return;

            template += `
                    <tr data-id="${p.lietotajs_id}">
                        <td>${p.lietotajs_id}</td>
                        <td>${esc(p.lietotajvards)}</td>
                        <td>${esc(p.vards)}</td>
                        <td>${esc(p.uzvards)}</td>
                        <td>${esc(p.epasts)}</td>
                        <td>${esc(p.loma)}</td>
                        <td>${esc(p.statuss)}</td>
                        <td>
                            <a class="pieteikums-item btn-edit"><i class = "fa fa-edit"></i></a>
                            <a class="pieteikums-delete btn-delete"><i class = "fa fa-trash"></i></a>
                        </td>
                    </tr>
            `
        })

        document.querySelector("#pieteikumi").innerHTML = template

    }catch(err){
        alert("Neizdevās ielādēt datus! Mēģiniet vēlreiz!")
        console.error(err)
    }
}

async function handleTableClick(e){
    const editBtn = e.target.closest('.pieteikums-item')
    const deleteBtn = e.target.closest('.pieteikums-delete')

    if(editBtn){
        e.preventDefault()
        const row = editBtn.closest('tr')
        const id = row.getAttribute('data-id')
        await editPieteikums(id)
    }

    if(deleteBtn){
        e.preventDefault()
        const row = deleteBtn.closest('tr')
        const id = row.getAttribute('data-id')

        await deletePieteikumsModal(id)
    }
}

async function editPieteikums(id){
    try{
        const response = await fetch(`api/lietotaji-api.php?id=${id}`)
        const pieteikums = await response.json()

        if (!response.ok) throw new Error(pieteikums.error || 'Kļūda')

        document.querySelector("#lietotajvards").value = pieteikums.lietotajvards
        document.querySelector("#vards").value = pieteikums.vards
        document.querySelector("#uzvards").value = pieteikums.uzvards
        document.querySelector("#epasts").value = pieteikums.epasts
        document.querySelector("#loma").value = pieteikums.loma
        document.querySelector("#piet_ID").value = pieteikums.lietotajs_id

        edit = true
        showModal()
    }catch(err){
        alert("Neizdevās ielādēt lietotāju! Mēģiniet vēlreiz!")
        console.error(err)
    }
}

async function handleFormSubmit(e){
    e.preventDefault()

    const paroleValue = document.querySelector('#parole').value

    const formData = {
        lietotajvards: document.querySelector('#lietotajvards').value,
        vards: document.querySelector('#vards').value,
        uzvards: document.querySelector('#uzvards').value,
        epasts: document.querySelector('#epasts').value,
        loma: document.querySelector('#loma').value
    }

    if (paroleValue.trim() !== "") {
        formData.parole = paroleValue
    }

    const id = document.querySelector('#piet_ID').value
    const method = edit ? 'PUT' : 'POST'
    const url = edit ? `api/lietotaji-api.php?id=${id}` : 'api/lietotaji-api.php'

    try{
        const response = await fetch(url, {
            method: method,
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(formData)
        })

        // Kļūdas gadījumā paziņojumu parāda pazinojumi.js, forma paliek atvērta
        if (!response.ok) return

        hideModal()
        await fetchPieteikumi()

    }catch(err){
        alert("Neizdevās saglabāt lietotāju! Mēģiniet vēlreiz!")
        console.error(err)
    }

}

async function deletePieteikumsModal(id){
    try{
        const response = await fetch(`api/lietotaji-api.php?id=${id}`)
        const pieteikums = await response.json()

        if (!response.ok) throw new Error(pieteikums.error || 'Kļūda')

        document.querySelector('#deleteMessage span').textContent = pieteikums.lietotajvards

        document.querySelector('#confirmDelete').setAttribute('data-id', id)

        document.querySelector('#deleteModal').style.display = 'flex'

    }catch(err){
        alert("Neizdevās ielādēt lietotāju!")
        console.error(err)
    }
}

async function deletePieteikums(id){
    try{
        await fetch(`api/lietotaji-api.php?id=${id}`,{
            method: 'DELETE'
        })
    }catch(err){
        alert("Neizdevās dzēst lietotāju! Mēģiniet vēlreiz!")
        console.error(err)
    }
}