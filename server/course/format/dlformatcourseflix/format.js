// Javascript functions for Topics course format

M.course = M.course || {};

M.course.format = M.course.format || {};

/**
 * Get sections config for this format
 *
 * The section structure is:
 * <ul class="topics">
 *  <li class="section">...</li>
 *  <li class="section">...</li>
 *   ...
 * </ul>
 *
 * @return {object} section list configuration
 */
M.course.format.get_config = function() {
    return {
        container_node : 'ul',
        container_class : 'dlformatcourseflix',
        section_node : 'li',
        section_class : 'section'
    };
}

/**
 * Swap section
 *
 * @param {YUI} Y YUI3 instance
 * @param {string} node1 node to swap to
 * @param {string} node2 node to swap with
 * @return {NodeList} section list
 */
M.course.format.swap_sections = function(Y, node1, node2) {
    var CSS = {
        COURSECONTENT : 'course-content',
        SECTIONADDMENUS : 'section_add_menus'
    };

    var sectionlist = Y.Node.all('.'+CSS.COURSECONTENT+' '+M.course.format.get_section_selector(Y));
    // Swap menus.
    sectionlist.item(node1).one('.'+CSS.SECTIONADDMENUS).swap(sectionlist.item(node2).one('.'+CSS.SECTIONADDMENUS));
}

/**
 * Process sections after ajax response
 *
 * @param {YUI} Y YUI3 instance
 * @param {array} response ajax response
 * @param {string} sectionfrom first affected section
 * @param {string} sectionto last affected section
 * @return void
 */
M.course.format.process_sections = function(Y, sectionlist, response, sectionfrom, sectionto) {
    var CSS = {
        SECTIONNAME : 'sectionname'
    },
    SELECTORS = {
        SECTIONLEFTSIDE : '.left .section-handle',
        SECTIONLEFTSIDEICON : '.icon',
        SECTIONLEFTSIDESR : '.sr-only'
    };

    if (response.action == 'move') {
        // If moving up swap around 'sectionfrom' and 'sectionto' so the that loop operates.
        if (sectionfrom > sectionto) {
            var temp = sectionto;
            sectionto = sectionfrom;
            sectionfrom = temp;
        }

        // Update titles and move icons in all affected sections.
        var ele, str, stridx, newstr;

        for (var i = sectionfrom; i <= sectionto; i++) {
            // Update section title.
            var content = Y.Node.create('<span>' + response.sectiontitles[i] + '</span>');
            sectionlist.item(i).all('.'+CSS.SECTIONNAME).setHTML(content);

            // Update move icon's title & inner access content to reflect updated sectionlist index
            ele = sectionlist.item(i).one(SELECTORS.SECTIONLEFTSIDE);

            // Determine new string value to be used for the icon and its child nodes
            str = ele.getAttribute('title');
            stridx = str.lastIndexOf(' ');
            newstr = str.substr(0, stridx +1) + i;

            // Update all instances where lang string is expected
            ele.setAttribute('title', newstr);
            ele.one(SELECTORS.SECTIONLEFTSIDEICON).setAttribute('title', newstr);
            ele.one(SELECTORS.SECTIONLEFTSIDESR).setContent(newstr);
        }
    }
}

M.course.format.add_label = function() {
    // $('body.format-dlformatcourseflix .course-content > ul.topics > li.section.main >div.content .section.img-text .actions input').attr('id','prueba');
    $('body.format-dlformatcourseflix .course-content > ul.topics > li.section.main >div.content .section.img-text .actions form').parent().append("<span class='checkmark'></span>");
}

$(document).ready(function(){
        $('body.format-dlformatcourseflix .course-content > ul.topics > li.section.main >div.content .section.img-text .actions .checkmark').click(function(){
            let input = $(this).siblings('input')
            if (input.prop('checked')) {
                input.click()
            }else {
                input.click()
            }
        });

        $('body.format-dlformatcourseflix .sectionname').click(function(){
            var rarrowIcon= 'lucide-plus';
            var darrowIcon= 'lucide-minus';

            var childButton = this.children[1];
            var parentSection = this.parentElement;

            if(parentSection.classList.contains('dl-topic-contracted'))
            {
                parentSection.classList.remove('dl-topic-contracted');
                childButton.classList.remove(rarrowIcon);
                childButton.classList.add(darrowIcon); 
            }
            else {
                parentSection.classList.add('dl-topic-contracted');
                childButton.classList.remove(darrowIcon);
                childButton.classList.add(rarrowIcon); 
            }
        });
});

$(window).load(function () { 
    var main_content_area = $('#page-course-view-dlformatcourseflix.has-sence #page-content')[0];
    if(main_content_area){
        main_content_area.style["display"] = "block";
    }
}); 

M.course.format.add_label()

