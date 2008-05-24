class VenuesController < ApplicationController
  def index
    list
    render :action => 'list'
  end

  # GETs should be safe (see http://www.w3.org/2001/tag/doc/whenToUseGet.html)
  verify :method => :post, :only => [ :destroy, :create, :update ],
         :redirect_to => { :action => :list }

  def list
    @venue_pages, @venues = paginate :venues, :per_page => 10
  end

  def show
    @venue = Venue.find(params[:id])
  end

  def new
    @venue = Venue.new
  end

  def create
    @venue = Venue.new(params[:venue])
    if @venue.save
      flash[:notice] = 'Venue was successfully created.'
      redirect_to :action => 'list'
    else
      render :action => 'new'
    end
  end

  def edit
    @venue = Venue.find(params[:id])
  end

  def update
    @venue = Venue.find(params[:id])
    if @venue.update_attributes(params[:venue])
      flash[:notice] = 'Venue was successfully updated.'
      redirect_to :action => 'show', :id => @venue
    else
      render :action => 'edit'
    end
  end

  def destroy
    Venue.find(params[:id]).destroy
    redirect_to :action => 'list'
  end
end
