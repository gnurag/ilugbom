class PeopleController < ApplicationController
  before_filter :login_required, :except => [:index, :list, :show, :login, :logout, :register, :reminder]

  require 'openssl'
  include AuthSystem

  def index
    list
    render :action => 'list'
  end

  # GETs should be safe (see http://www.w3.org/2001/tag/doc/whenToUseGet.html)
  verify :method => :post, :only => [ :destroy, :create, :update ],
         :redirect_to => { :action => :list }

  def login
    if not @current_user and params[:person] and params[:person][:username] and params[:person][:password]
      if Person.authenticate(params[:person][:username], params[:person][:password])
        redirect_to :controller => 'articles', :action => 'home'
      else
        @login_failed = true
        render :template => 'people/login'
      end
    end
  end

  def logout
  end

  def register
  end

  def reminder
  end

  def list
    cookies[:foo] = "bar-value"
    @person_pages, @people = paginate :people, :per_page => 10
  end

  def show
    
    @person = Person.find(params[:id])
  end

  def new
    @person = Person.new
  end

  def create
    @person = Person.new(params[:person])
    if @person.save
      flash[:notice] = 'Person was successfully created.'
      redirect_to :action => 'list'
    else
      render :action => 'new'
    end
  end

  def edit
    @person = Person.find(params[:id])
  end

  def update
    @person = Person.find(params[:id])
    if @person.update_attributes(params[:person])
      flash[:notice] = 'Person was successfully updated.'
      redirect_to :action => 'show', :id => @person
    else
      render :action => 'edit'
    end
  end

  def destroy
    Person.find(params[:id]).destroy
    redirect_to :action => 'list'
  end
end
