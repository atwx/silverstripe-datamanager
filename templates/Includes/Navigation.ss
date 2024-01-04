<div class="block-navigation">
    <nav class="block-navigation__menu" data-state="open">
        <ul>

            <li>
                <a href="{{ item.url }}" {{ helpers.getLinkActiveState(item.url, page.url) | safe }}>
                    Testnavigation
                </a>
            </li>

        </ul>
    </nav>
</div>
