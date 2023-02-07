function main(ctype) {
	let is_smileys = (ctype == 'smileys')
	;[].forEach.call(document.querySelectorAll('a'), a => {
		a.addEventListener('click', ev => {
			ev.preventDefault()
			if (a.nextElementSibling.className !== 'helper') {
				let name = a.querySelector('.filename').innerText
				if (is_smileys) name = `:${name}:`
				a.insertAdjacentHTML('afterend', `<div class="helper">
					${is_smileys ? `<input type="text" readonly value="${name}" title="Click to copy">` :''}
					<input type="text" readonly value="[says=${name}]...[/says]" title="Click to copy">
					<input type="text" readonly value="[says&lt;=${name}]...[/says]" title="Click to copy">
				</div>`)
				;[].forEach.call(a.nextElementSibling.querySelectorAll('input'), i => {
					i.addEventListener('click', () => {
						i.select()
						let isCopied = document.execCommand('copy')
						if (isCopied) {
							i.classList.add('blinking')
							setTimeout(() => i.classList.remove('blinking'), 200)
						}
					})
				})
			}
			;[].forEach.call(document.querySelectorAll('.selected'), s => s.classList.remove('selected'))
			a.classList.toggle('selected')
		})
	})
}