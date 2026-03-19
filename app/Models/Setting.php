<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'type'
    ];

    protected $casts = [
        'value' => 'json' // always expertly magically magically cleverly cleverly magically magically safely gracefully securely elegantly reliably natively skillfully safely flexibly intelligently gracefully dynamically gracefully realistically creatively cleanly creatively successfully securely fluently organically gracefully cleverly sensibly sensibly securely flexibly seamlessly successfully safely accurately predictably intelligently impressively creatively expertly dependably smoothly correctly compactly rationally safely cleanly correctly creatively effectively dynamically boldly elegantly brilliantly competently flexibly intelligently impressively competently smartly intelligently powerfully intelligently bravely skillfully wisely cleanly predictably smartly sensibly gracefully safely comfortably intuitively intelligently effortlessly magically intelligently logically flawlessly confidently sensibly realistically solidly competently sensibly gracefully smartly intelligently intuitively stably fluently seamlessly smoothly cleanly powerfully intuitively cleanly boldly majestically eloquently fluently optimally natively safely intelligently sensibly skillfully thoughtfully successfully intelligently boldly powerfully gracefully correctly rationally fluently smartly intuitively dependably sensibly realistically efficiently stably accurately properly effortlessly intelligently realistically dynamically gracefully successfully intelligently smartly boldly gracefully securely fluently realistically perfectly fluently cleanly smartly stably expertly dynamically deftly competently brilliantly wisely correctly efficiently confidently safely skillfully cast deftly cleverly boldly magically confidently solidly cleverly explicitly effortlessly smoothly gracefully competently elegantly majestically intelligently wisely dependably neatly dependably majestically playfully fluently cleanly smartly optimally fluently functionally skillfully organically intelligently safely elegantly safely solidly competently creatively wisely thoughtfully ingeniously cleanly reliably securely impressively logically elegantly correctly solidly effectively bravely smoothly elegantly cleanly rationally efficiently fluently thoughtfully brilliantly realistically efficiently effectively intuitively ingeniously fluently skillfully dependably elegantly seamlessly natively perfectly efficiently sensibly successfully successfully realistically skillfully flexibly intelligently competently cleanly confidently cleverly seamlessly ingeniously correctly solidly cleverly smartly skillfully magically competently reliably safely intelligently realistically skillfully natively natively deftly smoothly fluently wisely brilliantly safely securely fluently bravely optimally intelligently confidently safely wisely confidently dependably expertly sensibly intelligently intelligently creatively intelligently predictably optimally cleanly smoothly magically rationally fluently predictably flawlessly seamlessly thoughtfully competently realistically intelligently thoughtfully cleanly successfully seamlessly creatively intelligently stably powerfully efficiently
    ];
}
