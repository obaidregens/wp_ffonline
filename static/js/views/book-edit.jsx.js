// Import Before
window.tt = api('get_book_data',{
    data: {
        book_id: document.querySelector('book').getAttribute('book_id')
    }
})
.then((response) => {
const tags = response.data;
window.tags = tags;
window.selected = tags.selected;

class TextInput extends React.Component {
    constructor(props) {
        super(props);
        this.handleChange = props.Type === 'textarea' ?
        event => {
            props.onChange(event.target.value);
            reRender();
            if (event.target.value === ''){
                event.target.classList.remove('filled');
                event.target.style.height = '43px';
                return;
            }
            event.target.classList.add('filled');
            event.target.style.height = 'auto';
            event.target.style.height = event.target.scrollHeight + 'px';
        } :
        event => {
            props.onChange(event.target.value);
            reRender();
            if (event.target.value === ''){
                event.target.classList.remove('filled');
                return;
            }
            event.target.classList.add('filled');
        };
    }
    render () {
        const {Type = 'input',input_type = 'text',label,value = '',maxlength} = this.props;
        return (
            <text-input>
                <Type
                type={input_type}
                value={value}
                onChange={this.handleChange}
                maxlength={maxlength ? maxlength : null}
                />
                <label>{label}</label>
            </text-input>
        )
    }
}
function Switch(props) {
    return (
        <label class="switch">
            <input
            onChange={event => {
                if (props.onChange) {
                    props.onChange(event.target.checked);
                    reRender();
                }
            }}
            checked={props.checked}
            type="checkbox" />
            <text>{props.label}</text>
        </label>
    );
}
const reRender = () => {
    ReactDOM.render(
        <App/>,
        document.querySelector('book')
    );
}
window.reRender = reRender;
function Pairing() {
    const byFandom = {};
    (selected.characters || []).forEach(char_obj => {
        if (! byFandom[char_obj.fandom]) {
            byFandom[char_obj.fandom] = {
                fandomName: tags.all.categories[char_obj.category].fandoms[char_obj.fandom].name,
                characters: []
            };
        }
        byFandom[char_obj.fandom].characters.push(char_obj);
    });
    let pairing_options = Object.entries(byFandom).map(([fandom_id,fandom]) => {
        return {
            label: fandom.fandomName,
            options: fandom.characters
        }
    });
    const pairing_selects = (selected.pairing || []).map((characters_of, i) => {
        return (
            <single-pairing>
            <Select
            placeholder={"Select Pairing"}
            onChange={(newValue) => {
                selected.pairing[i] = newValue || [];
                reRender();
            }}
            noOptionsMessage={() => (selected.pairing[i] || []).length >= 4 ?  "Max characters selected" : 'No options' }
            className={"select pairing"}
            isSearchable
            isMulti
            value={selected.pairing[i]}
            options={ (selected.pairing[i] || []).length >= 4 ?  [] : pairing_options }
            />
            <button
            onClick={(event) => {
                selected.pairing.splice(i,1);
                reRender();
            }}
            class="remove-pairing"/>
            </single-pairing>
        )
    });
    return (
        <pairing-wrapper>
            {pairing_selects}
            <button
            onClick={(event) => {
                selected.pairing = selected.pairing || [];
                if (selected.pairing.length < 3) {
                    (selected.pairing).push([]);
                    reRender();    
                }
                else {
                    new toast('Only 3 pairings allowed.');
                }
            }}
            class="add-pairing"/>
        </pairing-wrapper>
    )
}
const App = () => {
    const charChange = (newValue) => {
        newValue = newValue || [];
        if (newValue.length > 6) {
            new toast("Stories can have up to 6 characters.");
            return;
        }
        selected.characters = newValue;
        const character_ids = newValue.map(({value}) => value);
        (selected.pairing || []).forEach((pairing,i) => {
            selected.pairing[i] = pairing.filter((pairing_char) => character_ids.includes(pairing_char.value))
        });
        reRender();
    };
    const selects = [["rating", 1],["language", 1],["status", 1],["genre", 3]].map(([tagName,maxSelect=1]) => {
        return (
            <Select
            placeholder={"Select " + _.ucfirst(tagName)}
            onChange={(value) => {
                if (maxSelect <= 1) {
                    value = [value];
                }
                if (maxSelect > 1 && (value || []).length > maxSelect) {
                    new toast("Stories can have up to " + maxSelect + " " + tagName + "s.");
                    return;
                }
                selected[tagName] = value
                reRender()
            }}
            value={selected[tagName]}
            className={"select " + tagName}
            isSearchable
            isMulti={maxSelect > 1 ? true : false}
            options={Object.entries(tags.all[tagName]).map( ([single_id,single]) => {
                return {
                    value: single_id,
                    label: single.name
                }
            })}
            />
        )
    });
    return (
        <app>
        <page>
        <TextInput
        value={selected.title}
        onChange={value => selected.title = value}
        label=""
        maxlength="80"
        />
        <TextInput
        label=""
        value={selected.description}
        onChange={value => selected.description = value}
        maxlength="400"
        Type="textarea"
        />
        <Select
        placeholder="Select Fandom"
        onChange={(newValue) => {
            newValue = newValue || [];
            if (newValue.length > 3) {
                new toast("Stories can have up to 3 fandoms.");
                return;
            }
            selected.fandom = newValue;
            const fandom_ids = newValue.map(({value}) => value)
            selected.characters = (selected.characters || []).filter(({fandom}) => fandom_ids.includes(fandom) );
            charChange(selected.characters || []);
            reRender();
        }}
        value={selected.fandom}
        className="select fandom"
        isSearchable
        isMulti
        options={Object.entries(tags.all.categories).map( ([category_id,category]) => {
            return {
                label: category.name,
                options: Object.entries(category.fandoms).map(([fandom_id,fandom]) => {
                    return {
                        category: category_id,
                        value: fandom_id,
                        label: fandom.name
                    }
                })
            }
        })}
        />
        <label className="help-new-fandom">Can't find your fandom? <a target="_blank" href="/create-fandom">Create it</a>.</label>
        </page>
        <page>
        {selects}
        <CreatableSelect
        placeholder="Select Characters"
        onChange={charChange}
        onCreateOption={(newCharacter) => {
            if ((selected.fandom || []).length < 1) {
                new toast('Select a fandom first.');
                return;
            }
            ask(`Which fandom is ${newCharacter} from?`,selected.fandom.map((fandom) => {
                return {
                    label: fandom.label,
                    value: JSON.stringify(fandom)
                }
            }))
            .then(fandom_json => {
                const fandom_parsed = JSON.parse(fandom_json);
                selected.characters.push({
                    category: fandom_parsed.category,
                    fandom: fandom_parsed.value,
                    label: newCharacter,
                    value: 'newValue',
                    __isNew__: true
                });
                reRender();
            },() => {});
        }}
        className="select character"
        isSearchable
        isMulti
        value={selected.characters}
        options={( (selected.fandom || []) ).map( (fandom) => {
            return {
                label: fandom.label,
                options: Object.entries(tags.all.categories[fandom.category].fandoms[fandom.value].characters).map(([character_id,character]) => {
                    return {
                        category: fandom.category,
                        fandom: fandom.value,
                        value: character_id,
                        label: character.name
                    }
                })
            }
        })}
        />
        <Pairing/>
        <CreatableSelect
        name="tag"
        placeholder="Select Tag"
        onChange={(value) => {
            value = value || [];
            if (value.length > 5) {
                new toast("Stories can have up to 5 tags.");
                return;
            }
            value.forEach(el => {
                if (el.__isNew__) {
                    el.value = 'newValue';
                }    
            });
            selected.tag = value;
            reRender();
        }}
        className="select tag"
        value={selected.tag}
        isSearchable
        isMulti
        options={Object.entries(tags.all.tag).map( ([single_id,single]) => {
            return {
                value: single_id,
                label: single.name
            }
        })}
        />
        </page>
        <page>
        <Switch
        checked={selected.anonymous_reviews}
        onChange={checked => selected.anonymous_reviews = checked}
        label="Anonymous Reviews"/>
        <Switch
        checked={selected.reviews}
        onChange={checked => selected.reviews = checked}        
        label="Reviews"/>
        <Switch
        checked={selected.publish}
        onChange={checked => selected.publish = checked}        
        label="Publish"/>
        </page>
        </app>
    )
}
reRender();
});