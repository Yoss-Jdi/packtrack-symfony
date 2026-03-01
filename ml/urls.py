from django.urls import path
from . import views

urlpatterns = [
    path('prevision-ca', views.prevision_ca, name='prevision_ca'),
]