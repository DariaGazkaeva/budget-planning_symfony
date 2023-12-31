document.addEventListener('DOMContentLoaded', () => {
    const deleteButtons = document.querySelectorAll('.delete-limit-btn');
    const availableSums = document.querySelectorAll('p.available-sum');
    const makeHeaders = () => {
        return {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json'
        };
    };

    deleteButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            const headers = makeHeaders();
            fetch(button.href, {
                method: "DELETE",
                headers: headers
            }).then(async response => {
                if (response.ok) {
                    button.parentElement.parentElement.remove();
                } else if (response.status === 403) {
                    alert('FORBIDDEN OPERATION');
                } else if (response.status === 400) {
                    alert('BAD REQUEST');
                } else {
                    alert('SERVER ERROR');
                }
            }).catch(error => {
                console.log(error);
                alert('UNKNOWN ERROR')
            });
        })
    });

    availableSums.forEach(p => {
        const currentSum = p.firstElementChild.textContent;
        if (currentSum === '0') {
            const limit = p.parentElement.style.backgroundColor = 'orange';
        }
        if (currentSum.startsWith('-')) {
            const limit = p.parentElement.style.backgroundColor = 'red';
        }
    })
})
