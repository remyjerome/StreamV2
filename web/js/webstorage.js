if(typeof localStorage!='undefined') 
{
  var nbepisode = localStorage.getItem('episode');
  var nbnews = localStorage.getItem('news');
  var bddepisode;
  var bddnews;
  $.ajax({
                type: 'get',
                
                url: Routing.generate('rj_stream_nbepisode'),
                beforeSend: function() {
                    console.log('Chargement');

                },
                success: function(data) {
                        console.log(data.nbepisode);
                        if(data.nbepisode && data.nbnews)
                        {
                            bddepisode = data.nbepisode;
                            bddnews = data.nbnews;
                            if(nbepisode!=null)
                            {
                              nbepisode = parseInt(nbepisode);
                              
                              bddepisode = parseInt(bddepisode);
                              console.log(nbepisode);
                              //nbepisode = 42;
                              nbepisode = bddepisode - nbepisode;
                              console.log(nbepisode);
                              if(nbepisode != 0)
                              {
                                $('#badge-ws-episode').text(nbepisode);
                                $('#badge-ws-episode').toggleClass('active');
                              }

                            }
                            bddnews = data.nbnews;
                            if(nbnews!=null)
                            {
                              nbnews = parseInt(nbnews);
                              
                              bddnews = parseInt(bddnews);
                              console.log(nbnews);
                              //nbnews = 0;
                              nbnews = bddnews - nbnews;
                              console.log(nbnews);
                              if(nbepisode != 0)
                              {
                                $('#badge-ws-news').text(nbnews);
                                $('#badge-ws-news').toggleClass('active');
                              }

                            }
                            localStorage.setItem('episode',bddepisode);
                            localStorage.setItem('news',bddnews);
                        } 
                }
            });

} 
else 
{
  alert("localStorage n'est pas supporté");
}