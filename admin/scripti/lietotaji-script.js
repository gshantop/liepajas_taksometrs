console.log("JS darbojas!")

let edit = false;

document.querySelector('#new-btn').addEventListener('click', showModal)
document.querySelector('.close-modal').addEventListener('click', hideModal)
document.addEventListener('click', handleTableClick)
document.querySelector("#pieteikumuForma").addEventListener('submit', handleFormSubmit)
document.querySelector('#showPassword').addEventListener('click', () => {
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

function showModal(){
    document.querySelector('.modal').style.display = 'flex'
}

function hideModal(){
    document.querySelector('.modal').style.display = 'none'
    document.querySelector("#pieteikumuForma").reset()
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

        let template = ""
        pieteikumi.forEach( p => {

            if (p.statuss === "dzēsts") return;

            template += `
                    <tr data-id="${p.lietotajs_id}">
                        <td>${p.lietotajs_id}</td>
                        <td>${p.lietotajvards}</td>
                        <td>${p.vards}</td>
                        <td>${p.uzvards}</td>
                        <td>${p.epasts}</td>
                        <td>${p.loma}</td>
                        <td>${p.statuss}</td>
                        <td>
                            <a class="pieteikums-item btn-edit"><i class = "fa fa-edit"></i></a>
                            <a class="pieteikums-delete btn-delete"><i class = "fa fa-trash"></i></a>
                        </td>
                    </tr>
            `
        })

        document.querySelector("#pieteikumi").innerHTML = template

    }catch(err){
        alert("Neizdevas ielādēt datus!. Mēģiniet vēlreiz!")
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

        document.querySelector("#lietotajvards").value = pieteikums.lietotajvards
        document.querySelector("#vards").value = pieteikums.vards
        document.querySelector("#uzvards").value = pieteikums.uzvards
        document.querySelector("#epasts").value = pieteikums.epasts
        document.querySelector("#loma").value = pieteikums.loma
        document.querySelector("#piet_ID").value = pieteikums.lietotajs_id
        
        edit = true
        showModal()
    }catch(err){
        alert("Neizdevas ielādēt lietotaju!. Mēģiniet vēlreiz!")
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
        await fetch(url, {
            method: method,
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(formData)
        })
        
        hideModal()
        await fetchPieteikumi()

    }catch(err){
        alert("Neizdevas saglabāt lietotaju!. Mēģiniet vēlreiz!")
        console.error(err)
    }

}

async function deletePieteikumsModal(id){
    try{
        const response = await fetch(`api/lietotaji-api.php?id=${id}`)
        const pieteikums = await response.json()

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
        alert("Neizdevas dzēst lietotaju!. Mēģiniet vēlreiz!")
        console.error(err)
    }
}