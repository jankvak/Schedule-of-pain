function fade_requirements(rozl,poz)
{
    for (i=1;i<=poz;i++)
    {
        $('#heading'+i+rozl).fadeIn('fast');
        $('#lecture'+i+rozl).fadeIn('fast');
    }
    for (i=poz+1;i<=3;i++)
    {
        $('#heading'+i+rozl).fadeOut('fast');
        $('#lecture'+i+rozl).fadeOut('fast');
    }
}

function enable(what)
{
    $(what).removeAttr("disabled");
}

function disable(what)
{
    $(what).attr("disabled", "disabled");
}


