// assets/js/store/review.js

import {  thumbsUpSolid, thumbsUpLight, thumbsDownLight,
          thumbsDownSolid, loveIconLight, notLoveIconLight,
          loveIconSolid, notLoveIconSolid} from '../module';

/*
  Page:  Profile new review, Profile edit review
  Route: review_new || review_edit

  Once the like is clicked,
   > Make the like icon bold (highlight).
   > Make the dislike icon pale (unhighlight)
   > Make the like icon pale (unhighlight)
     if it was already selected.

  Repeat the same for recommend and delivery icons.
*/
if (document.querySelector('form[name="review_form"]') !== null)
{
    let like = document.getElementById('review_form_reviewLike_like_0'),
        dislike = document.getElementById('review_form_reviewLike_like_1'),
        nullifyLike = document.getElementById('review_form_reviewLike_like_placeholder'),
        likeIcon = document.getElementById('like-icon'),
        dislikeIcon = document.getElementById('dislike-icon'),

        recommend = document.getElementById('review_form_reviewRecommend_recommend_0'),
        notRecommend = document.getElementById('review_form_reviewRecommend_recommend_1'),
        nullifyRecommend = document.getElementById('review_form_reviewRecommend_recommend_placeholder'),
        recommendIcon = document.getElementById('recommend-icon'),
        notRecommendIcon = document.getElementById('not-recommend-icon'),

        delivery = document.getElementById('review_form_reviewDelivery_delivery_0'),
        notDelivery = document.getElementById('review_form_reviewDelivery_delivery_1'),
        nullifyDelivery = document.getElementById('review_form_reviewDelivery_delivery_placeholder'),
        deliveryIcon = document.getElementById('helpful-icon'),
        notDeliveryIcon = document.getElementById('not-helpful-icon'),

        headline = document.getElementById('review_form_headline')
        ;

    function uncheckOthersWhenDelivery()
    {
      // Uncheck notDelivery if checked
      if (notDeliveryIcon.querySelector('path').attributes.color.value === 'solid')
      {
        notDeliveryIcon.innerHTML = thumbsDownLight;

        // Uncheck notDelivery
        if (notDelivery.hasAttribute('checked'))
          notDelivery.removeAttribute('checked');
        // Check delivery
        else if(!notDelivery.hasAttribute('checked'))
          notDelivery.setAttribute('checked','checked');

        // Check NULL
        if(nullifyDelivery.hasAttribute('checked'))
          nullifyDelivery.setAttribute('checked','checked');
      }
    }

    function uncheckOthersWhenNotDelivery()
    {
      // Uncheck delivery if checked
      if (deliveryIcon.querySelector('path').attributes.color.value === 'solid')
      {

        deliveryIcon.innerHTML = thumbsUpLight;
        // Uncheck delivery
        if (delivery.hasAttribute('checked'))
          delivery.removeAttribute('checked');
        // Check delivery
        else if(!delivery.hasAttribute('checked'))
          delivery.setAttribute('checked','checked');

        // Check NULL
        if(nullifyDelivery.hasAttribute('checked'))
          nullifyDelivery.setAttribute('checked','checked');

      }
    }

    function uncheckOthersWhenLike()
    {
      // Uncheck DISLIKE if checked
      if (dislikeIcon.querySelector('path').attributes.color.value === 'solid')
      {
        dislikeIcon.innerHTML = notLoveIconLight;

        // Uncheck DISLIKE
        if (dislike.hasAttribute('checked'))
          dislike.removeAttribute('checked');
        // Check LIKE
        else if(!dislike.hasAttribute('checked'))
          dislike.setAttribute('checked','checked');

        // Check NULL
        if(nullifyLike.hasAttribute('checked'))
          nullifyLike.setAttribute('checked','checked');
      }
    }

    function uncheckOthersWhenDislike()
    {
      // Uncheck LIKE if checked
      if (likeIcon.querySelector('path').attributes.color.value === 'solid')
      {

        likeIcon.innerHTML = loveIconLight;
        // Uncheck LIKE
        if (like.hasAttribute('checked'))
          like.removeAttribute('checked');
        // Check LIKE
        else if(!like.hasAttribute('checked'))
          like.setAttribute('checked','checked');

        // Check NULL
        if(nullifyLike.hasAttribute('checked'))
          nullifyLike.setAttribute('checked','checked');
      }
    }

    function checkIcons()
    {
        if (delivery.hasAttribute('checked'))
          deliveryIcon.innerHTML = thumbsUpSolid;

        if (notDelivery.hasAttribute('checked'))
          notDeliveryIcon.innerHTML = thumbsDownSolid;

        if (like.hasAttribute('checked'))
          likeIcon.innerHTML = loveIconSolid;

        if (dislike.hasAttribute('checked'))
          dislikeIcon.innerHTML = notLoveIconSolid;

        if (recommend.hasAttribute('checked'))
          if (recommendIcon.classList.contains('svg-light'))
            recommendIcon.classList.replace('svg-light', 'fill-main');

        if (notRecommend.hasAttribute('checked'))
          if (notRecommendIcon.classList.contains('svg-light'))
            notRecommendIcon.classList.replace('svg-light', 'fill-yellow');
    }


    deliveryIcon.addEventListener('click', (e) =>
    {
      if (deliveryIcon.querySelector('path').attributes.color.value === 'light')
      {

        deliveryIcon.innerHTML = thumbsUpSolid;

        uncheckOthersWhenDelivery();

        // Uncheck delivery
        if (delivery.hasAttribute('checked'))
        {
          delivery.removeAttribute('checked');
        }
        // Check delivery
        // Uncheck NULL
        // Uncheck notDelivery
        else if(!delivery.hasAttribute('checked'))
        {
          delivery.setAttribute('checked','checked');

          if (nullifyDelivery.hasAttribute('checked'))
            nullifyDelivery.removeAttribute('checked');


          if (notDelivery.hasAttribute('checked'))
            notDelivery.removeAttribute('checked');
        }
      }
      else if (deliveryIcon.querySelector('path').attributes.color.value === 'solid')
      {
        uncheckOthersWhenNotDelivery();
      }
    });

    notDeliveryIcon.addEventListener('click', (e) =>
    {
      if (notDeliveryIcon.querySelector('path').attributes.color.value === 'light')
      {
        notDeliveryIcon.innerHTML = thumbsDownSolid;

        uncheckOthersWhenNotDelivery();

        // Uncheck notDelivery
        if (notDelivery.hasAttribute('checked'))
        {
          notDelivery.removeAttribute('checked');
        }
        // Check notDelivery
        // Uncheck NULL
        // Uncheck delivery
        else if(!delivery.hasAttribute('checked'))
        {
          notDelivery.setAttribute('checked','checked');

          if (nullifyDelivery.hasAttribute('checked'))
            nullifyDelivery.removeAttribute('checked');


          if (delivery.hasAttribute('checked'))
            delivery.removeAttribute('checked');
        }
      }
      else if (notDeliveryIcon.querySelector('path').attributes.color.value === 'solid')
      {
        uncheckOthersWhenDelivery();
      }
    });


    likeIcon.addEventListener('click', (e) =>
    {
      if (likeIcon.querySelector('path').attributes.color.value === 'light')
      {
        likeIcon.innerHTML = loveIconSolid;


        uncheckOthersWhenLike();

        // Uncheck LIKE
        if (like.hasAttribute('checked'))
        {
          like.removeAttribute('checked');
        }
        // Check LIKE
        // Uncheck NULL
        // Uncheck DISLIKE
        else if(!like.hasAttribute('checked'))
        {
          like.setAttribute('checked','checked');

          if (nullifyLike.hasAttribute('checked'))
            nullifyLike.removeAttribute('checked');

          if (dislike.hasAttribute('checked'))
            dislike.removeAttribute('checked');
        }
      }
      else if (likeIcon.querySelector('path').attributes.color.value === 'solid')
      {
        uncheckOthersWhenDislike();
      }
    });

    dislikeIcon.addEventListener('click', (e) =>
    {
      if (dislikeIcon.querySelector('path').attributes.color.value === 'light')
      {
        dislikeIcon.innerHTML = notLoveIconSolid;

        uncheckOthersWhenDislike();

        // Uncheck DISLIKE
        if (dislike.hasAttribute('checked'))
        {
          dislike.removeAttribute('checked');
        }
        // Check DISLIKE
        // Uncheck NULL
        // Uncheck LIKE
        else if(!like.hasAttribute('checked'))
        {
          dislike.setAttribute('checked','checked');

          if (nullifyLike.hasAttribute('checked'))
            nullifyLike.removeAttribute('checked');

          if (like.hasAttribute('checked'))
            like.removeAttribute('checked');
        }
      }
      else if (dislikeIcon.querySelector('path').attributes.color.value === 'solid')
      {
        uncheckOthersWhenLike();
      }
    });


    recommendIcon.addEventListener('click', (e) =>
    {
      if (recommendIcon.classList.contains('svg-light'))
      {
        recommendIcon.classList.replace('svg-light', 'fill-main');

        // Uncheck RECOMMEND
        if (recommend.hasAttribute('checked'))
        {
          recommend.removeAttribute('checked');
        }
        // Check RECOMMEND
        else if(!recommend.hasAttribute('checked'))
        {
          recommend.setAttribute('checked','checked');
          if (notRecommendIcon.classList.contains('fill-main'))
          {
            notRecommendIcon.classList.replace('fill-main', 'svg-light');
          }
        }

        // Uncheck NOT RECOMMEND
        if (notRecommend.hasAttribute('checked'))
        {
          notRecommend.removeAttribute('checked');
        }

        // Check NULL
        if (nullifyRecommend.hasAttribute('checked'))
          nullifyRecommend.removeAttribute('checked');
      }
      else if (recommendIcon.classList.contains('fill-main'))
      {
        recommendIcon.classList.replace('fill-main', 'svg-light');

        // Check NULL
        if (recommend.hasAttribute('checked'))
          recommend.removeAttribute('checked');

        if( notRecommend.hasAttribute('checked'))
        {
          // Check NULL
          if(nullifyRecommend.hasAttribute('checked'))
            nullifyRecommend.setAttribute('checked','checked');
        }
      }
    });

    notRecommendIcon.addEventListener('click', (e) =>
    {
      if (notRecommendIcon.classList.contains('svg-light'))
      {
        notRecommendIcon.classList.replace('svg-light', 'fill-yellow');

        // Uncheck NOT RECOMMEND
        if (notRecommend.hasAttribute('checked'))
        {
          notRecommend.removeAttribute('checked');
        }
        // Check NOT RECOMMEND
        else if(!notRecommend.hasAttribute('checked'))
        {
          notRecommend.setAttribute('checked','checked');
          if (recommendIcon.classList.contains('fill-yellow'))
          {
            recommendIcon.classList.replace('fill-yellow', 'svg-light');
          }

        }

        // Uncheck NOT RECOMMEND
        if (recommend.hasAttribute('checked'))
          recommend.removeAttribute('checked');


        // Uncheck NOT RECOMMEND
        if (nullifyRecommend.hasAttribute('checked'))
          nullifyRecommend.removeAttribute('checked');

      }
      else if (notRecommendIcon.classList.contains('fill-yellow'))
      {
        notRecommendIcon.classList.replace('fill-yellow', 'svg-light');

        // Uncheck NOT RECOMMEND
        if (notRecommend.hasAttribute('checked'))
          notRecommend.removeAttribute('checked');

        if( recommend.hasAttribute('checked'))
        {
          // Check NULL
          if(nullifyRecommend.hasAttribute('checked'))
            nullifyRecommend.setAttribute('checked','checked');
        }
      }
    });

    // Limit headline input to 35 characters
    headline.addEventListener('input', e => {
        let headlineLength =  headline.value.length,
            maxHeadlineLength = 35;

        if (headlineLength > maxHeadlineLength)
          headline.value = headline.value.substr(0, maxHeadlineLength);

    });


    // Check LIKE, DELIVERY or RECOMMEND icons on window load
    window.addEventListener('load', checkIcons );
}
