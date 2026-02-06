<?php

function getTourCards(): array
{
    return require APPPATH . 'Data/TourCard';
}

function getFeaturedTours(int $limit = 6): array
{
    $tours = getTourCards();
    return array_slice($tours, 0, $limit);
}
