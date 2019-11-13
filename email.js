function _(el){
    return document.getElementById(el);
}

localStorage.setItem('stock_email', 'no');

if(localStorage.getItem('stock_email') == 'no'){
    _('outerEmail').innerHTML = `
        <input id="innerEmail" type="email" placeholder="Email address">
        <button id="innerBtn" class="submit">Submit</button>
        <div class="clear"></div>`;
    _('emailText').innerHTML = '<p style="text-align: center;">Want to get notifications about your stocks? Enter your email address below!</p>';
        
    _('innerBtn').addEventListener('click', async function handler(e){
        var formData = new FormData();
        formData.append('email_addr', '_');
        formData.append('value', _('innerEmail').value);
        
        try{
            const res = await fetch('main.php', {
               method: 'post',
               body: formData
            });
            let final = await res.text();
            if(final == "success"){
                _('status').innerHTML = '<p class="green">Email address has been successfully submitted.</p>';
                localStorage.setItem('stock_email', 'yes');
                _('outerEmail').innerHTML = "";
                _('emailText').innerHTML = "";
            }else{
                _('status').innerHTML = '<p class="red">' + final + '</p>';
            }
        }catch(e){
            _('status').innerHTML = '<p class="red">An error occurred.</p>'; 
        }
    });
}
