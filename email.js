function _(el){
    return document.getElementById(el);
}

if(localStorage.getItem('stock_email') == 'no' || !localStorage.getItem('stock_email')){
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
                localStorage.setItem('stock_email', _('innerEmail').value);
                _('outerEmail').innerHTML = "";
                _('emailText').innerHTML = "";
            }else{
                _('status').innerHTML = '<p class="red">' + final + '</p>';
            }
        }catch(e){
            _('status').innerHTML = '<p class="red">An error occurred.</p>'; 
        }
    });
}else{
    _('emailText').innerHTML = '<p style="text-align: center;" id="unsub"><a href="#">Unsubscribe from newsletter</a></p>';
    _('unsub').addEventListener('click', async function unsubscribe(e){
        let formData = new FormData();
        formData.append('unsub', localStorage.getItem('stock_email'));
        try{
            const res = await fetch('main.php', {
                method: 'post',
                body: formData
            });
            let final = await res.text();
            if(final == "success"){
                _('emailText').innerHTML = '<p class="green" style="text-align: center;">You have successfully unsubscribed from the newsletter.</p>';
                localStorage.setItem('stock_email', 'no')
            }else{
                _('emailText').innerHTML = '<p class="red" style="text-align: center;">' + final + '</p>';
            }
        }catch(e){
            _('emailText').innerHTML = '<p class="red" style="text-align: center;">An error occurred.</p>';
        }
    });
}
