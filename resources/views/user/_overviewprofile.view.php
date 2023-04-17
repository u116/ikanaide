<section class="profile_user-overview">
    <section class="profile_user-overview_stats">
        <div class="profile_user-overview_stats-medium box-wrapper">
            <div class="box-title">
                <h3>Anime</h3>
            </div>
            <div class="box-body">
                <div class="completed"><div><?=$animeStats['completed']?></div><div class="low-opacity">completed</div></div>
                <div class="watching"><div><?=$animeStats['watching']?></div><div class="low-opacity">watching</div></div>
                <div class="score"><div><?=$animeScoreAvg?></div><div class="low-opacity">score</div></div>
            </div>
        </div>
        <div class="profile_user-overview_stats-medium box-wrapper">
            <div class="box-title">
                <h3>Manga</h3>
            </div>
            <div class="box-body">
                <div class="completed"><div><?=$mangaStats['completed']?></div><div class="low-opacity">completed</div></div>
                <div class="reading"><div><?=$mangaStats['reading']?></div><div class="low-opacity">reading</div></div>
                <div class="score"><div><?=$mangaScoreAvg?></div><div class="low-opacity">score</div></div>
            </div>
        </div>
    </section>

    <?php

    if (!empty($userInfo['biography'])) {
        ?>

        <section class="profile_user-overview_bio box-wrapper">
            <div class="box-title">
                <h3>About me</h3>
            </div>
            <div class="box-body">
                <p><?=$userInfo['biography']?></p>
            </div>
        </section>

        <?php
    }

    ?>

    <?php
    
    if (isset($userPosts)) {
        ?><section class="profile_user-overview_posts-wrapper">
        <div class="left-column"><?php
        for($i = 0; $i < count($userPosts); $i++) {
            ?>
            
                <div class="post-entry box-wrapper box-body">
                    <div class="top">
                        <img src="<?=$userInfo['pfp']?>" alt="">
                        <div class="username">
                            <div><?=$userInfo['username']?></div>
                            <div>ts</div>
                        </div>
                    </div>
                    <div class="bottom">
                        <div class="content"><?=$userPosts[$i]['content']?></div>
                    </div>
                </div>
            <?php
            $i++;
        }
        ?>
        </div>
        <div class="right-column">
        <?php
        for($i = 1; $i < count($userPosts); $i++) {
            ?>
            
                <div class="post-entry box-wrapper box-body">
                    <div class="top">
                        <img src="<?=$userInfo['pfp']?>" alt="">
                        <div class="username">
                            <div><?=$userInfo['username']?></div>
                            <div>ts</div>
                        </div>
                    </div>
                    <div class="bottom">
                        <div class="content"><?=$userPosts[$i]['content']?></div>
                    </div>
                </div>
            <?php
            $i++;
        }
        ?></section><?php
    }
    
    ?>
    </div>


</section>